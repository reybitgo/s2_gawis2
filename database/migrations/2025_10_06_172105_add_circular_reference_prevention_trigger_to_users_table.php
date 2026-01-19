<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared('DROP PROCEDURE IF EXISTS check_circular_sponsor_reference');
            DB::unprepared('
                CREATE PROCEDURE check_circular_sponsor_reference(
                    IN p_user_id INT,
                    IN p_sponsor_id INT
                )
                BEGIN
                    DECLARE v_current_id INT;
                    DECLARE v_depth INT DEFAULT 0;
                    DECLARE v_max_depth INT DEFAULT 100;

                    IF p_user_id = p_sponsor_id THEN
                        SIGNAL SQLSTATE "45000"
                            SET MESSAGE_TEXT = "A user cannot sponsor themselves.";
                    END IF;

                    SET v_current_id = p_sponsor_id;

                    WHILE v_current_id IS NOT NULL AND v_depth < v_max_depth DO
                        IF v_current_id = p_user_id THEN
                            SIGNAL SQLSTATE "45000"
                                SET MESSAGE_TEXT = "Circular sponsor reference detected. The selected sponsor is already in your downline network.";
                        END IF;

                        SELECT sponsor_id INTO v_current_id
                        FROM users
                        WHERE id = v_current_id;

                        SET v_depth = v_depth + 1;
                    END WHILE;

                    IF v_depth >= v_max_depth THEN
                        SIGNAL SQLSTATE "45000"
                            SET MESSAGE_TEXT = "Maximum sponsor chain depth exceeded. Possible circular reference.";
                    END IF;
                END
            ');

            DB::unprepared('DROP TRIGGER IF EXISTS before_users_update_check_circular_sponsor');
            DB::unprepared('
                CREATE TRIGGER before_users_update_check_circular_sponsor
                BEFORE UPDATE ON users
                FOR EACH ROW
                BEGIN
                    IF NEW.sponsor_id IS NOT NULL AND (OLD.sponsor_id IS NULL OR NEW.sponsor_id != OLD.sponsor_id) THEN
                        CALL check_circular_sponsor_reference(NEW.id, NEW.sponsor_id);
                    END IF;
                END
            ');

            DB::unprepared('DROP TRIGGER IF EXISTS before_users_insert_check_circular_sponsor');
            DB::unprepared('
                CREATE TRIGGER before_users_insert_check_circular_sponsor
                BEFORE INSERT ON users
                FOR EACH ROW
                BEGIN
                    IF NEW.sponsor_id IS NOT NULL THEN
                        IF NEW.id IS NOT NULL AND NEW.id = NEW.sponsor_id THEN
                            SIGNAL SQLSTATE "45000"
                                SET MESSAGE_TEXT = "A user cannot sponsor themselves.";
                        END IF;
                    END IF;
                END
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_users_update_check_circular_sponsor');
        DB::unprepared('DROP TRIGGER IF EXISTS before_users_insert_check_circular_sponsor');
        DB::unprepared('DROP PROCEDURE IF EXISTS check_circular_sponsor_reference');
    }
};
