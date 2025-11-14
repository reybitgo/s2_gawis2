@extends('layouts.frontend')

@section('content')
    <!-- Page Header Start -->
    <div class="page-header">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Page Header Box Start -->
                    <div class="page-header-box">
                        <h1 class="text-anime-style-3" data-cursor="-opaque">
                            The <span>Opportunity</span>
                        </h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/') }}">home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    opportunity
                                </li>
                            </ol>
                        </nav>
                    </div>
                    <!-- Page Header Box End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Our Approach Section Start -->
    <div class="our-approach">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-6">
                    <!-- Section Title Start -->
                    <div class="section-title">
                        <h3 class="wow fadeInUp">Our Compensation Plan</h3>
                        <h2 class="text-anime-style-3" data-cursor="-opaque">
                            A Classic Unilevel <span>MLM System</span>
                        </h2>
                    </div>
                    <!-- Section Title End -->
                </div>

                <div class="col-lg-6">
                    <!-- Section Title Content Start -->
                    <div class="section-title-content wow fadeInUp" data-wow-delay="0.2s">
                        <p>
                            Our system is a classic Unilevel MLM Compensation Plan. This is a popular and straightforward model where distributors can sponsor an unlimited number of people on their frontline (Level 1), and commissions are paid down through a fixed number of levels.
                        </p>
                    </div>
                    <!-- Section Title Content End -->
                </div>
            </div>

            <div class="row approach-list">
                <div class="col-lg-4 col-md-6">
                    <!-- Approach Item Start -->
                    <div class="approach-item wow fadeInUp">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-approach-1.svg') }}" alt="" />
                        </div>
                        <div class="approach-item-content">
                            <h3>Fixed Depth</h3>
                            <p>
                                Commissions are paid on a 5-level deep structure, providing a clear and achievable path to earning.
                            </p>
                        </div>
                    </div>
                    <!-- Approach Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Approach Item Start -->
                    <div class="approach-item active wow fadeInUp" data-wow-delay="0.2s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-approach-2.svg') }}" alt="" />
                        </div>
                        <div class="approach-item-content">
                            <h3>Hybrid Commission Model</h3>
                            <p>
                                Earn from both a primary commission from the sale of a "Starter Package" and a separate "Unilevel Bonus" from regular product sales.
                            </p>
                        </div>
                    </div>
                    <!-- Approach Item End -->
                </div>

                <div class="col-lg-4 col-md-6">
                    <!-- Approach Item Start -->
                    <div class="approach-item wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon-box">
                            <img src="{{ asset('frontend/images/icon-approach-3.svg') }}" alt="" />
                        </div>
                        <div class="approach-item-content">
                            <h3>Real-Time Processing</h3>
                            <p>
                                Commissions are calculated and distributed instantly, a strong technical advantage for our members.
                            </p>
                        </div>
                    </div>
                    <!-- Approach Item End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Approach Section End -->

    <!-- Oue Excellence Section Start -->
    <div class="our-excellence">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-6">
                    <!-- Section Title Start -->
                    <div class="section-title">
                        <h3 class="wow fadeInUp">Proven Strategies</h3>
                        <h2 class="text-anime-style-3" data-cursor="-opaque">
                            Inspired by Industry <span>Leaders</span>
                        </h2>
                    </div>
                    <!-- Section Title End -->
                </div>

                <div class="col-lg-6">
                    <!-- Section Button Start -->
                    <div class="section-btn wow fadeInUp" data-wow-delay="0.2s">
                        <a href="/register" class="btn-default">Join Now</a>
                    </div>
                    <!-- Section Button End -->
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-6">
                    <!-- Excellence Image Start -->
                    <div class="excellence-image">
                        <figure class="image-anime reveal">
                            <img src="{{ asset('frontend/images/excellence-image.jpg') }}" alt="" />
                        </figure>
                    </div>
                    <!-- Excellence Image End -->
                </div>

                <div class="col-lg-6">
                    <!-- Excellence Content Start -->
                    <div class="excellence-content">
                        <!-- Excellence Item Start -->
                        <div class="excellence-item wow fadeInUp">
                            <h3>Customer Loyalty Focus</h3>
                            <p>
                                Our model is inspired by the success of companies that prioritize customer loyalty through high-quality products and a clear, rewarding compensation plan.
                            </p>
                        </div>
                        <!-- Excellence Item End -->

                        <!-- Excellence Item Start -->
                        <div class="excellence-item wow fadeInUp" data-wow-delay="0.2s">
                            <h3>Product Movement Bonuses</h3>
                            <p>
                                Our compensation plan rewards the movement of products to end consumers, ensuring a sustainable business model for our members.
                            </p>
                        </div>
                        <!-- Excellence Item End -->

                        <!-- Excellence Item Start -->
                        <div class="excellence-item wow fadeInUp" data-wow-delay="0.4s">
                            <h3>Wellness Niche Expertise</h3>
                            <p>
                                Our compensation plan is tailored to the wellness niche, drawing inspiration from successful companies in the immune system support and wellness industry.
                            </p>
                        </div>
                        <!-- Excellence Item End -->
                    </div>
                    <!-- Excellence Content End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Oue Excellence Section End -->
@endsection
