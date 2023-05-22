jQuery(document).ready(function ($) {
  // Function to update the price in the mini cart
  function updateMiniCartPrice() {
    var cartPrice = $(".product-price").text();
    $(".mini-cart-price").text(cartPrice);
  }

  // Update the price when the cart page loads
  updateMiniCartPrice();

  // Update the price when the cart is updated dynamically (e.g., through AJAX)
  $(document.body).on("updated_cart_totals", function () {
    updateMiniCartPrice();
  });

  // AJAX call
  // $(document).on("click", ".reset_variations", function () {
  //   $(".dynamic-table").hide();
  //   $("..dynamic-price").hide();
  // });
  $(document).on("change", 'input[id^="quantity_"]', function () {
    var new_quantity = $(this).val();
    var product_id = $('button[name="add-to-cart"]').val();
    var variationId = $(".variation_id").val();
    variationId = variationId ?? 0;
    product_id = product_id ?? 0;
    console.log(new_quantity);
    console.log(product_id);
    console.log(variationId);

    $.ajax({
      url: csp_guest_ajax.ajaxurl,
      type: "POST",
      data: {
        action: "csp_update_quantity",
        product_id: product_id,
        variationId: variationId,
        new_quantity: new_quantity,
      },
      success: function (response) {
        var responseData = JSON.parse(response);
        console.log(responseData);
        // console.log(typeof responseData);
        // Check if 'table' key exists
        if (responseData.hasOwnProperty("table")) {
          // console.log(responseData.table);
          $(".dynamic-table").html(responseData.table);
        }
        var price = parseFloat(responseData.price);
        // console.log(price);
        price = price.toFixed(2);

        // console.log(typeof response);
        $(".dynamic-price").text(price);
      },
      error: function (xhr, status, error) {
        // Handle errors here
        console.log(error);
      },
    });

    // // Get the new value of the input field
    // var newValue = $(this).val();
    // // Display an alert with the new value
    // alert("The new value is: " + newValue);
  });

  // jQuery(document).on("found_variation", function (event, variation) {
  //   // $(".dynamic-table").show();
  //   // $(".dynamic-price").show();
  //   // Get the variation ID
  //   // var variationId = variation.variation_id;
  //   var product_id = $()

  //   $.ajax({
  //     url: csp_guest_ajax.ajaxurl,
  //     type: "POST",
  //     data: {
  //       action: "csp_update_var__quantity",
  //       variationId: variationId,
  //       new_quantity: new_quantity,
  //     },
  //     success: function (response) {
  //       // response = parseFloat(response);
  //       // response = response.toFixed(2);
  //       // // console.log(typeof response);
  //       // $(".dynamic-price").text(response);
  //     },
  //     error: function (xhr, status, error) {
  //       // Handle errors here
  //       console.log(error);
  //     },
  //   });
  //   // Output the variation ID or perform any other actions
  //   alert("Current Variation ID: " + variationId);
  // });
});
