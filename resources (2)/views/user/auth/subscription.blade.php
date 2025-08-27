<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/animate.css">
    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/icofont.min.css">
    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/meanmenu.css">
    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/magnific-popup.min.css">
    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/nice-select.css">
    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/style.css">
    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/dark.css">
    <link rel="stylesheet" href="{{ asset('landing') }}/assets/css/responsive.css">

    <title>Me3Co.com</title>

    <link rel="icon" type="image/png" href="{{ asset('landing') }}/assets/img/favicon.png">
</head>

<body id="body" class="auth-page vh-100 ">
    <!-- Log In page -->
    <section class="features-area book-features ptb-100" id="pricing">
        <div class="container">
            <div class="section-title">
                <h2>Get a Subscription Plan</h2>
                <div class="bar"></div>
            </div>

            <div class="row g-3 align-items-center justify-content-center">
                @php
                    $plans = get_subscriptions();
                @endphp

                @foreach ($plans as $plan)
                    <div class="col-lg-3 col-md-6">
                        <div class="pricing-table price1">
                            <div class="price-header">
                                <h3 class="title">{{ $plan->name }}</h3>
                            </div>
                            <div class="pricing-content p-3 text-white">

                                <ul class="planinfolist mt-3">
                                    @foreach (get_plan_feature($plan->description) as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="price-footer">
                                @if (!$plan->type)
                                    <a href="{{ route('project') }}" class="btn btn-warning">
                                        Continue
                                    </a>
                                @else
                                    <button type="button" class="btn btn-warning"
                                        data-plan-id="{{ $plan->id }}">
                                        $ {{ $plan->price }}
                                    </button>
                                @endif()

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="shape7"><img src="{{ asset('landing') }}/assets/img/shape7.png" alt="shape"></div>
        <div class="shape3"><img src="{{ asset('landing') }}/assets/img/shape3.png" alt="img"></div>
        <div class="bg-gray shape-1"></div>
        <div class="shape6"><img src="{{ asset('landing') }}/assets/img/shape6.png" alt="img"></div>
        <div class="shape8 rotateme"><img src="{{ asset('landing') }}/assets/img/shape8.svg" alt="shape"></div>
        <div class="shape9"><img src="{{ asset('landing') }}/assets/img/shape9.svg" alt="shape"></div>
        <div class="shape10"><img src="{{ asset('landing') }}/assets/img/shape10.svg" alt="shape"></div>
        <div class="shape11 rotateme"><img src="{{ asset('landing') }}/assets/img/shape11.svg" alt="shape"></div>
    </section>
    <!--end container-->
    @if (Session::has('message'))
        <div class="toast custom show position-fixed align-items-center text-white bg-primary border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    {!! Session::get('message') !!}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="custom-modal p-3">
        <input type="checkbox" id="price-form" class="modal-check-box d-none" />
        <form id="payment-form">
            <h5 class="my-2">Check Out</h5>
            <div id="link-authentication-element">
                <!--Stripe.js injects the Link Authentication Element-->
            </div>
            <div id="payment-element">
                <!--Stripe.js injects the Payment Element-->
            </div>
            <button id="submit" class="btn btn-primary w-100 my-2">
                <div class="spinner hidden" id="spinner"></div>
                <span id="button-text">Pay now</span>
            </button>
            <div id="payment-message" class="hidden"></div>
        </form>
    </div>
    <label for="price-form" class="backdrop"></label>
    <!-- App js -->
    <script src="https://js.stripe.com/v3/"></script>
    <script src="{{ asset('landing') }}/assets/js/jquery.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/canvas.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/jquery.meanmenu.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/wow.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/tilt.jquery.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/jquery.magnific-popup.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/owl.carousel.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/jquery.nice-select.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/form-validator.min.js"></script>
    <script src="{{ asset('landing') }}/assets/js/contact-form-script.js"></script>
    <script src="{{ asset('landing') }}/assets/js/main.js"></script>
    <script>
        let stripe = Stripe("{{ env('STRIPE_PUBLIC_KEY') }}")
        let intentUrl = "{{ route('subscription.intent') }}/"
        let plans = document.querySelectorAll('[data-plan-id]');
        let paymentElements;
        plans.forEach((element) => {
            element.addEventListener('click', async (e) => {
                let planId = e.target.getAttribute('data-plan-id')
                let url = intentUrl + planId

                let response = await fetch(url)
                let data = await response.json();
                openModal();
                let {
                    clientSecret
                } = data;
                paymentElements = stripe.elements({
                    clientSecret
                });

                const linkAuthenticationElement = paymentElements.create("linkAuthentication");
                linkAuthenticationElement.mount("#link-authentication-element");

                const paymentElementOptions = {
                    layout: "tabs",
                };

                const paymentElement = paymentElements.create("payment", paymentElementOptions);
                paymentElement.mount("#payment-element");
            })
        })

        document.querySelector("#payment-form").addEventListener("submit", handleSubmit);

        function openModal() {
            let modal = document.querySelector('#price-form');
            modal?.click()
        }
        async function handleSubmit(e) {
            e.preventDefault();

            const {
                error
            } = await stripe.confirmPayment({
                elements: paymentElements,
                confirmParams: {
                    // Make sure to change this to your payment completion page
                    return_url: `{{ route('subscription.confirm') }}`,
                },
            });

            // This point will only be reached if there is an immediate error when
            // confirming the payment. Otherwise, your customer will be redirected to
            // your `return_url`. For some payment methods like iDEAL, your customer will
            // be redirected to an intermediate site first to authorize the payment, then
            // redirected to the `return_url`.
            if (error.type === "card_error" || error.type === "validation_error") {
                showMessage(error.message);
            } else {
                showMessage("An unexpected error occurred.");
            }
        }

        function showMessage(messageText) {
            const messageContainer = document.querySelector("#payment-message");

            messageContainer.classList.remove("hidden");
            messageContainer.textContent = messageText;

            setTimeout(function() {
                messageContainer.classList.add("hidden");
                messageText.textContent = "";
            }, 4000);
        }
    </script>
</body>

</html>
