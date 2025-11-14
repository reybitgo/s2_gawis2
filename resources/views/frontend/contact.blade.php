@extends('layouts.frontend')

@section('content')
    <!-- Page Header Start -->
    <div class="page-header">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <!-- Page Header Box Start -->
                    <div class="page-header-box">
                        <h1 class="text-anime-style-3" data-cursor="-opaque">
                            Contact <span>us</span>
                        </h1>
                        <nav class="wow fadeInUp">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/') }}">home</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    contact us
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

    <!-- Page Contact Us Start -->
    <div class="page-contact-us">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    @php
                        $adminUser = \App\Models\User::role('admin')->first();
                    @endphp
                    <div class="contact-info-box">
                        @if($adminUser && $adminUser->phone)
                        <!-- Contact Info Item Start -->
                        <div class="contact-info-item wow fadeInUp">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-phone-accent.svg') }}" alt="" />
                            </div>
                            <div class="contact-info-content">
                                <p>Call Us</p>
                                <h3>
                                    <a href="tel:{{ $adminUser->phone }}"><small>{{ $adminUser->phone }}</small></a>
                                </h3>
                            </div>
                        </div>
                        <!-- Contact Info Item End -->
                        @endif

                        @if($adminUser && $adminUser->email)
                        <!-- Contact Info Item Start -->
                        <div class="contact-info-item wow fadeInUp" data-wow-delay="0.2s">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-mail-accent.svg') }}" alt="" />
                            </div>
                            <div class="contact-info-content">
                                <p>Email address</p>
                                <h3>
                                    <a href="mailto:{{ $adminUser->email }}"><small>{{ $adminUser->email }}</small></a>
                                </h3>
                            </div>
                        </div>
                        <!-- Contact Info Item End -->
                        @endif

                        @if($adminUser && ($adminUser->address || $adminUser->city))
                        <!-- Contact Info Item Start -->
                        <div class="contact-info-item wow fadeInUp" data-wow-delay="0.4s">
                            <div class="icon-box">
                                <img src="{{ asset('frontend/images/icon-location.svg') }}" alt="" />
                            </div>
                            <div class="contact-info-content">
                                <p>location</p>                                
                                <h3><small>{{ $adminUser->address }}{{ $adminUser->city ? ', ' . $adminUser->city : '' }}{{ $adminUser->state ? ', ' . $adminUser->state : '' }}, Philippines {{ $adminUser->zip ? ' ' . $adminUser->zip : '' }}</small></h3>
                            </div>
                        </div>
                        <!-- Contact Info Item End -->
                        @endif
                    </div>
                    <!-- Contact Info Box End -->
                </div>

                <div class="col-lg-6">
                    <!-- Contact Us Image Start -->
                    <div class="contact-us-image">
                        <figure>
                            <img src="{{ asset('frontend/images/about-img-3.png') }}" alt="" />
                        </figure>
                    </div>
                    <!-- Contact Us Image End -->
                </div>

                <div class="col-lg-6">
                    <!-- Contact Us Form Start -->
                    <div class="contact-us-form">
                        <!-- Section Title Start -->
                        <div class="section-title">
                            <h2 class="text-anime-style-3" data-cursor="-opaque">
                                Get in <span>touch</span>
                            </h2>
                        </div>
                        <!-- Section Title End -->

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form id="contactForm" action="{{ route('contact.submit') }}" method="POST" class="contact-form">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6 mb-4">
                                    <input type="text" name="fname" class="form-control" id="fname" placeholder="First name" required value="{{ old('fname') }}"/>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-md-6 mb-4">
                                    <input type="text" name="lname" class="form-control" id="lname" placeholder="Last name" required value="{{ old('lname') }}"/>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-md-6 mb-4">
                                    <input type="email" name="email" class="form-control" id="email" placeholder="E-mail" required value="{{ old('email') }}"/>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-md-6 mb-4">
                                    <input type="text" name="phone" class="form-control" id="phone" placeholder="Phone" required value="{{ old('phone') }}"/>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-md-12 mb-5">
                                    <textarea name="message" class="form-control" id="message" rows="4" placeholder="Write Message..." required>{{ old('message') }}</textarea>
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="col-md-12">
                                    <button type="submit" class="btn-default">
                                        <span>submit message</span>
                                    </button>
                                    <div id="msgSubmit" class="h3 hidden"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- Contact Us Form End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Page Contact Us End -->

    <!-- Google Map Section Start -->
    <div class="google-map">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Google Map IFrame Start -->
                    <div class="google-map-iframe">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3831.1356165464686!2d120.50248!3d16.213466!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMTbCsDEyJzQ4LjUiTiAxMjDCsDMwJzA5LjIiRQ!5e0!3m2!1sen!2sph!4v1762071454702!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <!-- Google Map IFrame End -->
                </div>
            </div>
        </div>
    </div>
    <!-- Google Map Section End -->
@endsection