1. run "composer install"
2. Go to the public folder
3. run "php -S 127.0.0.1:8080"
4. Open Browser for url localhost:8080

When developing a new application, you only need to focus on two folders:
    1. App
    2. Public

~App~
The app folder contains the components for the MVC (Model View Controller) framework (see the corresponding documentation for more information).

~Public~
The public folder contains any document that is directly available to the user (images, css, js).

How to include a CSS file in my view?
    Css::load('filename');   // Note: do not include .css
    Css::loadAll(array("filename1", "filename2", "...", ...));

How to include a JS file in my view?
    Js::load('filename');   // Note: do not include .js
    Js::loadAll(array("filename1", "filename2", "...", ...));