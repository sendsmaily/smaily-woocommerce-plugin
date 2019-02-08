"use strict";

(function($) {
  // Display messages handler.
  function displayMessage(text) {
    var error =
      arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

    // Generate message.
    var message = document.createElement("div");
    // Add classes based on error / success.
    if (error) {
      message.classList.add("error");
      message.classList.add("notice");
      message.classList.add("is-dismissible");
    } else {
      message.classList.add("notice-success");
      message.classList.add("notice");
      message.classList.add("is-dismissible");
    }
    var paragraph = document.createElement("p");
    // Add text.
    paragraph.innerHTML = text;
    message.appendChild(paragraph);
    // Close button
    var button = document.createElement("BUTTON");
    button.classList.add("notice-dismiss");
    button.onclick = function() {
      $(this)
        .closest("div")
        .hide();
    };
    message.appendChild(button);
    // Append to message-display.
    document.querySelector(".message-display").appendChild(message);
  }

  // Top tabs handler.
  $("#tabs").tabs();
  // Add custom class for active tab.
  $("#tabs-list li a").click(function() {
    $("a.nav-tab-active").removeClass("nav-tab-active");
    $(this).addClass("nav-tab-active");
  });

  // Hide spinner.
  $(".loader").hide();

  // First Form on Settings page to check if subdomain / username / password are correct.
  $().ready(function() {
    $("#startupForm").submit(function(e) {
      e.preventDefault();
      var spinner = $(".loader");
      var validateButton = $("#validate-credentials-btn");
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
            displayMessage(data["error"], true);
            // Hide loading icon
            spinner.hide();
          } else if (!data) {
            displayMessage("Something went wrong with request to Smaily", true);
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
              // Abandoned cart autoresponders list.
              $("#cart-autoresponders-list").append(
                $("<option>", {
                  value: JSON.stringify({ name: item["name"], id: item["id"] }),
                  text: item["name"]
                })
              );
              // Success message.
              displayMessage("Smaily credentials sucessfully validated!");
            });
            // Hide validate button.
            validateButton.hide();
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
      var spinner = $(".loader");
      spinner.show();
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
          spinner.hide();
          // Response message from back-end.
          var data = $.parseJSON(response);
          if (data["error"]) {
            displayMessage(data["error"], true);
          } else if (!data) {
            displayMessage("Something went wrong with saving data!", true);
          } else {
            displayMessage(data["success"]);
          }
        }
      );
      return false;
    });
  });
})(jQuery);
