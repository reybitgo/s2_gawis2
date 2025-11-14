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
                            About <span>us</span>
                        </h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/') }}">home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    about us
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

    <!-- About Us Section Start -->
    <div class="about-us">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <!-- About Images Start -->
                    <div class="about-images">
                        <figure>
                            <img src="{{ asset('frontend/images/about-img-3.png') }}" alt="" />
                        </figure>
                    </div>
                    <!-- About Images End -->
                </div>

                <div class="col-lg-6">
                    <!-- About us Content Start -->
                    <div class="about-us-content">
                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h3 class="wow fadeInUp">about us</h3>
                            <h2 class="text-anime-style-3" data-cursor="-opaque">
                                Empowering Health & <span>Financial Freedom</span>
                            </h2>
                            <p class="wow fadeInUp" data-wow-delay="0.2s">
                                We are dedicated to providing high-quality health and wellness products through a powerful Unilevel MLM platform, creating opportunities for financial independence and a healthier lifestyle for our members.
                            </p>
                        </div>
                        <!-- Section Title End -->

                        <!-- About Us Body Start -->
                        <div class="about-us-body wow fadeInUp" data-wow-delay="0.4s">
                            <!-- About Us List Start -->
                            <div class="about-us-list">
                                <ul>
                                    <li>Premium Quality Health Products</li>
                                    <li>
                                        Lucrative Unilevel Compensation Plan
                                    </li>
                                </ul>
                            </div>
                            <!-- About Us List End -->

                            <!-- About Body Item Start -->
                            <div class="about-body-item">
                                <div class="icon-box">
                                    <img src="{{ asset('frontend/images/icon-about-body.svg') }}" alt="" />
                                </div>
                                <div class="about-body-item-title">
                                    <h3>100% Natural & Pure Ingredients</h3>
                                </div>
                            </div>
                            <!-- About Body Item End -->
                        </div>
                        <!-- About Us Body End -->

                        <!-- About Us Footer Start -->
                        <div class="about-us-footer wow fadeInUp" data-wow-delay="0.6s">
                            <!-- About Us Button Start -->
                            <div class="about-us-btn">
                                <a href="#" class="btn-default">Contact Now</a>
                            </div>
                            <!-- About Us Button End -->

                            <!-- About Contact Box Start -->
                            <div class="about-contact-box">
                                <div class="icon-box">
                                    <img src="{{ asset('frontend/images/icon-phone.svg') }}" alt="" />
                                </div>
                                <div class="about-contact-box-content">
                                    <p>Support Any Time</p>
                                    <h3>
                                        <a href="tel:{{ $admin->phone }}">{{ $admin->phone }}</a>
                                    </h3>
                                </div>
                            </div>
                            <!-- About Contact Box End -->
                        </div>
                        <!-- About Us Footer End -->
                    </div>
                    <!-- About us Content End -->
                </div>
            </div>
        </div>
    </div>
    <!-- About Us Section End -->

    <!-- Our Approach Section Start -->
    <div class="our-approach">
        <div class="container">
            <div class="row section-row align-items-center">
                <div class="col-lg-6">
                    <!-- Section Title Start -->
                    <div class="section-title">
                        <h3 class="wow fadeInUp">Our Model</h3>
                        <h2 class="text-anime-style-3" data-cursor="-opaque">
                            Inspired by Success, <span>Built for You</span>
                        </h2>
                    </div>
                    <!-- Section Title End -->
                </div>

                <div class="col-lg-6">
                    <!-- Section Title Content Start -->
                    <div class="section-title-content wow fadeInUp" data-wow-delay="0.2s">
                        <p>
                            Our Unilevel MLM plan is inspired by industry leaders like doTERRA and Young Living. We focus on a simple, rewarding structure that promotes both recruitment and long-term product consumption.
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
                            <h3>Our mission</h3>
                            <p>
                                To empower individuals with the opportunity to achieve financial freedom through a sustainable and rewarding business model, while promoting health and wellness.
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
                            <h3>Our vision</h3>
                            <p>
                                To become a leading MLM company, recognized for our high-quality products, transparent compensation plan, and commitment to our members' success.
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
                            <h3>Our value</h3>
                            <p>
                                Integrity, Quality, and Empowerment. We believe in providing the best products and a fair, transparent opportunity for everyone.
                            </p>
                        </div>
                    </div>
                    <!-- Approach Item End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Our Approach Section End -->
@endsection
