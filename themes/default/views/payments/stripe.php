<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Pay with Stripe</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="<?= base_url('themes/' . $settings->theme . '/assets/js/jquery.js'); ?>"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
    <style>
      * {
        box-sizing: border-box;
      }

      body {
        font-family: -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 16px;
        -webkit-font-smoothing: antialiased;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-content: center;
        height: 100vh;
        width: 100vw;
        padding: 0;
        margin: 0;
        background: #f5f5f9;
      }
      .loader,
      .loader:after {
        border-radius: 50%;
        width: 8em;
        height: 8em;
      }
      .loader {
        margin: 60px auto;
        font-size: 10px;
        position: relative;
        text-indent: -9999em;
        border-top: 0.25em solid rgba(175, 175, 175, 0.2);
        border-right: 0.25em solid rgba(175, 175, 175, 0.2);
        border-bottom: 0.25em solid rgba(175, 175, 175, 0.2);
        border-left: 0.25em solid #5469d4;
        -webkit-transform: translateZ(0);
        -ms-transform: translateZ(0);
        transform: translateZ(0);
        -webkit-animation: load8 1s infinite linear;
        animation: load8 1s infinite linear;
      }
      @-webkit-keyframes load8 {
        0% {
          -webkit-transform: rotate(0deg);
          transform: rotate(0deg);
        }
        100% {
          -webkit-transform: rotate(360deg);
          transform: rotate(360deg);
        }
      }
      @keyframes load8 {
        0% {
          -webkit-transform: rotate(0deg);
          transform: rotate(0deg);
        }
        100% {
          -webkit-transform: rotate(360deg);
          transform: rotate(360deg);
        }
      }

      form {
        width: 30vw;
        min-width: 500px;
        align-self: center;
        /* box-shadow: 0px 0px 0px 0.5px rgba(50, 50, 93, 0.1),
          0px 2px 5px 0px rgba(50, 50, 93, 0.1), 0px 1px 1.5px 0px rgba(0, 0, 0, 0.07);
        border-radius: 7px;
        padding: 40px; */
      }

      input {
        border-radius: 6px;
        margin-bottom: 6px;
        padding: 12px;
        border: 1px solid rgba(50, 50, 93, 0.1);
        height: 44px;
        font-size: 16px;
        width: 100%;
        background: white;
      }

      .result-message {
        line-height: 22px;
        font-size: 16px;
      }

      .hidden {
        display: none;
      }

      #card-error {
        color: red;
        text-align: left;
        font-size: 13px;
        line-height: 17px;
        margin-top: 12px;
      }

      #card-element {
        border-radius: 4px 4px 0 0;
        padding: 12px;
        border: 1px solid rgba(50, 50, 93, 0.1);
        height: 44px;
        width: 100%;
        background: white;
      }

      #payment-request-button {
        margin-bottom: 32px;
      }

      /* Buttons and links */
      .button, button {
        background: #5469d4;
        color: #ffffff;
        font-family: Arial, sans-serif;
        border-radius: 0 0 4px 4px;
        border: 0;
        padding: 12px 16px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: block;
        transition: all 0.2s ease;
        box-shadow: 0px 4px 5.5px 0px rgba(0, 0, 0, 0.07);
        width: 100%;
      }
      .button:hover, button:hover {
        filter: contrast(115%);
      }
      .button:disabled, button:disabled {
        opacity: 0.5;
        cursor: default;
      }

      .go-back .button {
        border-radius: 4px;
        text-align: center;
        text-decoration: none;
      }

      p {
        margin: 1rem 0;
      }

      /* spinner/processing state, errors */
      .spinner,
      .spinner:before,
      .spinner:after {
        border-radius: 50%;
      }
      .spinner {
        color: #ffffff;
        font-size: 22px;
        text-indent: -99999px;
        margin: 0px auto;
        position: relative;
        width: 20px;
        height: 20px;
        box-shadow: inset 0 0 0 2px;
        -webkit-transform: translateZ(0);
        -ms-transform: translateZ(0);
        transform: translateZ(0);
      }
      .spinner:before,
      .spinner:after {
        position: absolute;
        content: "";
      }
      .spinner:before {
        width: 10.4px;
        height: 20.4px;
        background: #5469d4;
        border-radius: 20.4px 0 0 20.4px;
        top: -0.2px;
        left: -0.2px;
        -webkit-transform-origin: 10.4px 10.2px;
        transform-origin: 10.4px 10.2px;
        -webkit-animation: loading 2s infinite ease 1.5s;
        animation: loading 2s infinite ease 1.5s;
      }
      .spinner:after {
        width: 10.4px;
        height: 10.2px;
        background: #5469d4;
        border-radius: 0 10.2px 10.2px 0;
        top: -0.1px;
        left: 10.2px;
        -webkit-transform-origin: 0px 10.2px;
        transform-origin: 0px 10.2px;
        -webkit-animation: loading 2s infinite ease;
        animation: loading 2s infinite ease;
      }

      @-webkit-keyframes loading {
        0% {
          -webkit-transform: rotate(0deg);
          transform: rotate(0deg);
        }
        100% {
          -webkit-transform: rotate(360deg);
          transform: rotate(360deg);
        }
      }
      @keyframes loading {
        0% {
          -webkit-transform: rotate(0deg);
          transform: rotate(0deg);
        }
        100% {
          -webkit-transform: rotate(360deg);
          transform: rotate(360deg);
        }
      }

      @media only screen and (max-width: 600px) {
        form {
          width: 90vw;
          min-width: 96%;
          margin: 16px auto;
        }
      }
    </style>
  </head>

  <body>
    <div class="loader" id="loader">Loading...</div>
    <!-- Display a payment form -->
    <?= form_open('card/charge/' . $inv->id, 'id="payment-form" style="display: none"'); ?>
    <h1><?=$settings->site_name;?></h1>
    <p>Please fill the form below to make payment.</p>
    <p style="font-weight: bold;">Invoice number <?= $inv->id; ?>, Reference number <?= $inv->reference_no; ?></p>
      <div id="card-element"><!--Stripe.js injects the Card Element--></div>
      <button id="submit">
        <div class="spinner hidden" id="spinner"></div>
        <span id="button-text"><?= lang('Pay') . ' (' . $settings->currency_prefix . ' ' . ($inv->grand_total - $paid) . ')'; ?></span>
      </button>
      <p id="card-error" role="alert"></p>
      <p class="result-message hidden"></p>
      <p class="go-back hidden">
      <a href="<?=site_url('clients/sales'); ?>" class="button">Go to Invoices</a>
      </p>
    </form>

    <script>
      setTimeout(function () {
        document.getElementById("loader").style.display = "none";
        document.getElementById("payment-form").style.display = "block";
      }, 1000);
      // A reference to Stripe.js initialized with a fake API key.
      // Sign in to see examples pre-filled with your key.
      var stripe = Stripe('<?= $stripe->publishable_key; ?>');

      // The items the customer wants to buy
      var purchase = {
        "<?=$this->security->get_csrf_token_name();?>": "<?=$this->security->get_csrf_hash()?>",
        items: [{ id: "<?= $inv->id; ?>" }],
      };

      // Disable the button until we have Stripe set up on the page
      document.querySelector("button").disabled = true;
      $.ajax({
          type: "post",
          url: "<?=site_url('payments/stripe_create/' . $inv->id);?>",
          data: purchase,
          success: function(data) {
            if(data.error) {
              document.querySelector("#card-error").innerHTML = data.error;
              // alert('<?=lang('ajax_error');?>'+' '+data.error);
            } else {
              var elements = stripe.elements();

              var style = {
                base: {
                  color: "#32325d",
                  fontFamily: "Arial, sans-serif",
                  fontSmoothing: "antialiased",
                  fontSize: "16px",
                  "::placeholder": {
                    color: "#32325d",
                  },
                },
                invalid: {
                  fontFamily: "Arial, sans-serif",
                  color: "#fa755a",
                  iconColor: "#fa755a",
                },
              };

              var card = elements.create("card", { style: style });
              // Stripe injects an iframe into the DOM
              card.mount("#card-element");
              card.on("change", function (event) {
                // Disable the Pay button if there are no card details in the Element
                document.querySelector("button").disabled = event.empty;
                document.querySelector("#card-error").textContent = event.error
                  ? event.error.message
                  : "";
              });

              var form = document.getElementById("payment-form");
              form.addEventListener("submit", function (event) {
                event.preventDefault();
                // Complete payment when the submit button is clicked
                payWithCard(stripe, card, data.clientSecret);
              });
            }
          },
          error: function(data) {
            console.log(data);
              alert('<?=lang('ajax_error');?>'+' '+data.error);
          }
      });

      var payWithCard = function (stripe, card, clientSecret) {
        loading(true);
        stripe
          .confirmCardPayment(clientSecret, {
            payment_method: {
              card: card,
            },
          })
          .then(function (result) {
            if (result.error) {
              // Show error to your customer
              showError(result.error.message);
            } else {
              // The payment succeeded!
              orderComplete(result.paymentIntent.id);
            }
          });
      };

      /* ------- UI helpers ------- */

      // Shows a success message when the payment is complete
      var orderComplete = function (paymentIntentId) {
        $.ajax({
          type: "post",
          url: "<?=site_url('payments/stripe_done/' . $inv->id);?>",
          data: {
            "paymentIntentId": paymentIntentId,
            "<?=$this->security->get_csrf_token_name();?>": "<?=$this->security->get_csrf_hash()?>",
          },
          success: function(data) {
            loading(false);
            if (data.success) {
              document.querySelector(".result-message").classList.remove("hidden");
              document.querySelector(".result-message").innerHTML = data.message;
              document.querySelector("button").disabled = true;
              document.querySelector(".go-back").classList.remove("hidden");
            } else {
              document.querySelector("#card-error").innerHTML = data.error;
            }
          },
          error: function() {
              document.querySelector("#card-error").innerHTML = '<?=lang('ajax_error');?>';
              alert('<?=lang('ajax_error');?>');
          }
        });
      };

      // Show the customer the error from Stripe if their card fails to charge
      var showError = function (errorMsgText) {
        loading(false);
        var errorMsg = document.querySelector("#card-error");
        errorMsg.textContent = errorMsgText;
        setTimeout(function () {
          errorMsg.textContent = "";
        }, 4000);
      };

      // Show a spinner on payment submission
      var loading = function (isLoading) {
        if (isLoading) {
          // Disable the button and show a spinner
          document.querySelector("button").disabled = true;
          document.querySelector("#spinner").classList.remove("hidden");
          document.querySelector("#button-text").classList.add("hidden");
        } else {
          document.querySelector("button").disabled = false;
          document.querySelector("#spinner").classList.add("hidden");
          document.querySelector("#button-text").classList.remove("hidden");
        }
      };
    </script>
  </body>
</html>
