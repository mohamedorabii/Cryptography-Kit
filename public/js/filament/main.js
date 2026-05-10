document.addEventListener("DOMContentLoaded", function () {

    // Add To Cart Buttons
    const addToCartButtons = document.querySelectorAll(".add-to-cart");

    addToCartButtons.forEach(button => {
        button.addEventListener("click", function () {
            alert("Product added to cart!");
        });
    });

});
