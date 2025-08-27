<!doctype html>
<html lang="en">
<head>
	<title>Me3Co.com</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="{{ asset('home') }}/css/home.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('home') }}/css/home-page-responsive.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('home') }}/css/utility.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
</head>
<body>
    <!-- Start Preloader Area -->
    {{-- <div class="preloader-area">
        <div class="lds-hourglass"></div>
    </div> --}}
    <!-- End Preloader Area -->

    <!-- Start Navbar Area -->
    @include('landing.header')
    <!-- End Navbar Area -->


    @yield('content')


    <!-- Start Footer Area -->
    @include('landing.footer')
    <!-- End Footer Area -->
<script type="text/javascript" src="{{ asset('home') }}/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script>
   
	const paragraphs = document.querySelectorAll('#myDiv a');

paragraphs.forEach(text => {
  text.addEventListener('click', () => {
    paragraphs.forEach(text => text.classList.remove('activeB'));
    text.classList.add('activeB');
  });
});

var menuButton = document.getElementById("menu-sticky-button");
         var closeButton = document.getElementById("close-button");
         var sidebar = document.getElementById("sidebar");

         menuButton.addEventListener("click", function () {
            sidebar.style.right = "0";
            menuContent.style.display = "block";
         });

         closeButton.addEventListener("click", function () {
            sidebar.style.right = "-250px";
            menuContent.style.display = "none";
         });

var header = document.querySelector(".bg-sticky");
         var prevScrollPos = window.pageYOffset;

         window.onscroll = function () {
            var currentScrollPos = window.pageYOffset;
            if (currentScrollPos > 360 && currentScrollPos) {
               header.style.top = "0";
            } else {
               header.style.top = "-100px";
            }
            prevScrollPos = currentScrollPos;
         };


 // Get all elements with the class 'create-project-btn'
    const buttons = document.querySelectorAll('.create-project-btn');

    // Get all elements with the class 'hidden'
    const hiddenElements = document.querySelectorAll('.hidden');

    // Get all images with the class 'project-img', 'blueprint-img', etc.
    const images = document.querySelectorAll('.project-img, .blueprint-img, .scale-img, .measuring-img, .measuring-report-img, .changing-formulas-img, .estimated-price-img');

    // Loop through each button and add a click event listener
    buttons.forEach((button, index) => {
        button.addEventListener('click', () => {
            // Remove 'active' class from all buttons
            buttons.forEach(btn => {
                btn.classList.remove('activeA');
            });

            // Add 'active' class to the clicked button
            button.classList.add('activeA');

            // Hide all elements with the class 'hidden'
            hiddenElements.forEach(element => {
                element.classList.add('hidden');
            });

            // Show the corresponding element based on the index
            hiddenElements[index].classList.remove('hidden');

            // Hide all images
            images.forEach(image => {
                image.classList.add('hidden');
            });

            // Show the corresponding image based on the index
            images[index].classList.remove('hidden');
        });
    });


        var menuButton = document.getElementById("menu-button");
         var closeButton = document.getElementById("close-button");
         var sidebar = document.getElementById("sidebar");

         menuButton.addEventListener("click", function () {
            sidebar.style.right = "0";
            menuContent.style.display = "block";
         });

         closeButton.addEventListener("click", function () {
            sidebar.style.right = "-250px";
            menuContent.style.display = "none";
         });
</script>
   
</body>

</html>
