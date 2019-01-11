"use strict";

(function($) {
  // Check if autoresponder data is allready validated.
  $(window).on("load", function() {
    // Form elements.
    var spinner = $(".loader");
    var advancedForm = $("#advancedForm");
    var startupForm = $("#startupForm");
    var loaderWraper = $(".loader-wraper");
    // Smaily credentials.
    var subdomain = $("#subdomain").val();
    var username = $("#username").val();
    var password = $("#password").val();
    // Validate automatically if set.
    if (subdomain != "" && username != "" && password != "") {
      // Show loading icon.
      spinner.show();
      // Call to WordPress API.
      $.post(
        ajaxurl,
        {
          action: "validate_api",
          form_data: startupForm.serialize()
        },
        function(response) {
          var data = $.parseJSON(response);
          // Show Error messages to user if any exist.
          if (data["error"]) {
            var errorMessage =
              '<div class = "error notice"><p>' + data["error"] + "</p></div>";
            $(".message-display").html(errorMessage);
            // Hide loading icon
            spinner.hide();
          } else if (!data) {
            var errorMessage =
              '<div class = "error notice"><p>Something went wrong with request to Smaily</p></div>"';
            $(".message-display").html(errorMessage);
            // Hide loading icon
            spinner.hide();
          } else {
            // Add autoresponders to autoresponders list inside next form.
            $.each(data, function(index, item) {
              // Sync autoresponders list.
              $("#autoresponders-list").append(
                $("<option>", {
                  value: JSON.stringify({ name: item["name"], id: item["id"] }),
                  text: item["name"]
                })
              );
              // Abandoned cart autoresponders list.
              $("#cart-autoresponders-list").append(
                $("<option>", {
                  value: JSON.stringify({ name: item["name"], id: item["id"] }),
                  text: item["name"]
                })
              );
            });
            // Hide validate button.
            loaderWraper.hide();
            // Show next form
            advancedForm.show();
            // Hide loading icon
            spinner.hide();
          }
        }
      );
    }
  });

  // First Form on Settings page to check if subdomain / username / password are correct.
  $().ready(function() {
    $("#startupForm").submit(function(e) {
      e.preventDefault();

      var spinner = $(".loader");
      var advancedForm = $("#advancedForm");
      var loaderWraper = $(".loader-wraper");

      // Show loading icon.
      spinner.show();
      var smly = $(this);

      // Call to WordPress API.
      $.post(
        ajaxurl,
        {
          action: "validate_api",
          form_data: smly.serialize()
        },
        function(response) {
          var data = $.parseJSON(response);
          // Show Error messages to user if any exist.
          if (data["error"]) {
            var errorMessage =
              '<div class = "error notice"><p>' + data["error"] + "</p></div>";
            $(".message-display").html(errorMessage);
            // Hide loading icon
            spinner.hide();
          } else if (!data) {
            var errorMessage =
              '<div class = "error notice"><p>Something went wrong with request to Smaily</p></div>"';
            $(".message-display").html(errorMessage);
            // Hide loading icon
            spinner.hide();
          } else {
            // Add autoresponders to autoresponders list inside next form.
            $.each(data, function(index, item) {
              // Sync autoresponders list
              $("#autoresponders-list").append(
                $("<option>", {
                  value: JSON.stringify({ name: item["name"], id: item["id"] }),
                  text: item["name"]
                })
              );
              // Abandoned cart autoresponders list
              $("#cart-autoresponders-list").append(
                $("<option>", {
                  value: JSON.stringify({ name: item["name"], id: item["id"] }),
                  text: item["name"]
                })
              );
            });
            // Hide validate button.
            loaderWraper.hide();
            // Show next form
            advancedForm.show();
            // Hide loader icon.
            spinner.hide();
          }
        }
      );
      return false;
    });

    // Second form on settings page to save user info to database.
    $("#advancedForm").submit(function(event) {
      event.preventDefault();
      // Scroll back to top if saved.
      $("html, body").animate(
        {
          scrollTop: "0px"
        },
        "slow"
      );
      var user_data = $("#startupForm").serialize();
      var api_data = $("#advancedForm").serialize();
      // Call to WordPress  API.
      $.post(
        ajaxurl,
        {
          action: "update_api_database",
          // Second form data.
          autoresponder_data: api_data,
          // First form data.
          user_data: user_data
        },
        function(response) {
          // Response message from back-end.
          var data = $.parseJSON(response);
          if (data["error"]) {
            var errorMessage =
              '<div class = "error notice"><p>' + data["error"] + "</p></div>";
            $(".message-display").html(errorMessage);
          } else if (!data) {
            var errorMessage =
              '<div class = "error notice"><p>Something went wrong with saving data!</p></div>"';
            $(".message-display").html(errorMessage);
            // Hide loading icon
            spinner.hide();
          } else {
            // Display message to user.
            var successMessage =
              '<div class = "notice notice-success is-dismissible"><p>' +
              data["success"] +
              "</p></div>";
            $(".message-display").html(successMessage);
          }
        }
      );
      return false;
    });
  });
})(jQuery);
