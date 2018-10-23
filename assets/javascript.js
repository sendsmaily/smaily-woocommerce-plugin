(function($) {
  // First Form on Settings page to check if subdomain / username / password are correct.
  $().ready(function() {
    $("#startupForm").submit(function(e) {
      e.preventDefault();

      const spinner = $(".loader");
      const advancedForm = $("#advancedForm");
      const loaderWraper = $(".loader-wraper");

      // Show loading icon.
      spinner.show();
      const $smly = $(this);

      // Call to WordPress API.
      $.post(
        ajaxurl,
        {
          action: "validate_api",
          form_data: $smly.serialize()
        },
        function(response) {
          const data = $.parseJSON(response);
          // Show Error messages to user if any exist.
          if (data["error"]) {
            $errorMessage = ` < div class = "error notice" > < p > ${
              data["error"]
            } < / p > < / div > `;
            $(".message-display").html($errorMessage);
            // Hide loading icon
            spinner.hide();
          } else {
            // If no errors show success message.
            $successMessage = ` < div class = "notice notice-success is-dismissible" > < p > Connection successful < / p > < / div > `;
            $(".message-display").html($successMessage);

            // Add autoresponders to autoresponders list inside next form.
            $.each(data, (index, item) => {
              $("#autoresponders-list").append(
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
    $("#advancedForm").submit(event => {
      event.preventDefault();
      // Scroll back to top if saved.
      $("html, body").animate(
        {
          scrollTop: "0px"
        },
        "slow"
      );
      const user_data = $("#startupForm").serialize();
      const api_data = $("#advancedForm").serialize();
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
          data = $.parseJSON(response);
          if (data["error"]) {
            $errorMessage = ` < div class = "error notice" > < p > ${
              data["error"]
            } < / p > < / div > `;
            $(".message-display").html($errorMessage);
          } else {
            // Display message to user.
            $successMessage = ` < div class = "notice notice-success is-dismissible" > < p > ${
              data["success"]
            } < / p > < / div > `;
            $(".message-display").html($successMessage);
          }
        }
      );
      return false;
    });
  });
})(jQuery);
