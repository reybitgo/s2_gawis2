<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\GenealogyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GenealogyController extends Controller
{
    protected $genealogyService;

    public function __construct(GenealogyService $genealogyService)
    {
        $this->genealogyService = $genealogyService;
    }

    public function showUnilevel()
    {
        $user = Auth::user();
        $data = $this->genealogyService->getGenealogyTree($user, 'unilevel_bonus');

        $breadcrumbs = [
            ['title' => 'Genealogy'],
            ['title' => 'Unilevel'],
        ];

        return view('member.genealogy.unilevel', [
            'tree' => $data['tree'],
            'stats' => $data['stats'],
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function showMlm()
    {
        $user = Auth::user();
        $data = $this->genealogyService->getGenealogyTree($user, 'mlm');

        $breadcrumbs = [
            ['title' => 'Genealogy'],
            ['title' => 'MLM'],
        ];

        return view('member.genealogy.mlm', [
            'tree' => $data['tree'],
            'stats' => $data['stats'],
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
