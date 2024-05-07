<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

2021-04-20 22:10:50 --> Payment has been made for Sale Reference #3790429551103483 via Stripe (pi_1IiKHDLkXRKe1IYhClOfFAje). (127.0.0.1)
Stripe\PaymentIntent Object
(
    [id] => pi_1IiKHDLkXRKe1IYhClOfFAje
    [object] => payment_intent
    [amount] => 10000
    [amount_capturable] => 0
    [amount_received] => 10000
    [application] => 
    [application_fee_amount] => 
    [canceled_at] => 
    [cancellation_reason] => 
    [capture_method] => automatic
    [charges] => Stripe\Collection Object
        (
            [object] => list
            [data] => Array
                (
                    [0] => Stripe\Charge Object
                        (
                            [id] => ch_1IiKHsLkXRKe1IYhiNpMRnC8
                            [object] => charge
                            [amount] => 10000
                            [amount_captured] => 10000
                            [amount_refunded] => 0
                            [application] => 
                            [application_fee] => 
                            [application_fee_amount] => 
                            [balance_transaction] => txn_1IiKHtLkXRKe1IYhJ4G2tHmR
                            [billing_details] => Stripe\StripeObject Object
                                (
                                    [address] => Stripe\StripeObject Object
                                        (
                                            [city] => 
                                            [country] => 
                                            [line1] => 
                                            [line2] => 
                                            [postal_code] => 63000
                                            [state] => 
                                        )

                                    [email] => 
                                    [name] => 
                                    [phone] => 
                                )

                            [calculated_statement_descriptor] => Stripe
                            [captured] => 1
                            [created] => 1618927848
                            [currency] => usd
                            [customer] => 
                            [description] => 
                            [destination] => 
                            [dispute] => 
                            [disputed] => 
                            [failure_code] => 
                            [failure_message] => 
                            [fraud_details] => Array
                                (
                                )

                            [invoice] => 
                            [livemode] => 
                            [metadata] => Stripe\StripeObject Object
                                (
                                )

                            [on_behalf_of] => 
                            [order] => 
                            [outcome] => Stripe\StripeObject Object
                                (
                                    [network_status] => approved_by_network
                                    [reason] => 
                                    [risk_level] => normal
                                    [risk_score] => 40
                                    [seller_message] => Payment complete.
                                    [type] => authorized
                                )

                            [paid] => 1
                            [payment_intent] => pi_1IiKHDLkXRKe1IYhClOfFAje
                            [payment_method] => pm_1IiKHlLkXRKe1IYhXbwwZjVj
                            [payment_method_details] => Stripe\StripeObject Object
                                (
                                    [card] => Stripe\StripeObject Object
                                        (
                                            [brand] => visa
                                            [checks] => Stripe\StripeObject Object
                                                (
                                                    [address_line1_check] => 
                                                    [address_postal_code_check] => pass
                                                    [cvc_check] => pass
                                                )

                                            [country] => DE
                                            [exp_month] => 12
                                            [exp_year] => 2022
                                            [fingerprint] => 5s4lALal2nhynyeG
                                            [funding] => credit
                                            [installments] => 
                                            [last4] => 3184
                                            [network] => visa
                                            [three_d_secure] => Stripe\StripeObject Object
                                                (
                                                    [authenticated] => 1
                                                    [authentication_flow] => challenge
                                                    [result] => authenticated
                                                    [result_reason] => 
                                                    [succeeded] => 1
                                                    [version] => 1.0.2
                                                )

                                            [wallet] => 
                                        )

                                    [type] => card
                                )

                            [receipt_email] => 
                            [receipt_number] => 
                            [receipt_url] => https://pay.stripe.com/receipts/acct_15LpChLkXRKe1IYh/ch_1IiKHsLkXRKe1IYhiNpMRnC8/rcpt_JL0IQ41nFfaXyoraMUGH26xxtHp8sJ4
                            [refunded] => 
                            [refunds] => Stripe\Collection Object
                                (
                                    [object] => list
                                    [data] => Array
                                        (
                                        )

                                    [has_more] => 
                                    [total_count] => 0
                                    [url] => /v1/charges/ch_1IiKHsLkXRKe1IYhiNpMRnC8/refunds
                                )

                            [review] => 
                            [shipping] => 
                            [source] => 
                            [source_transfer] => 
                            [statement_descriptor] => 
                            [statement_descriptor_suffix] => 
                            [status] => succeeded
                            [transfer_data] => 
                            [transfer_group] => 
                        )

                )

            [has_more] => 
            [total_count] => 1
            [url] => /v1/charges?payment_intent=pi_1IiKHDLkXRKe1IYhClOfFAje
        )

    [client_secret] => pi_1IiKHDLkXRKe1IYhClOfFAje_secret_chILdQUVkBE5S7Mne43bzXrv7
    [confirmation_method] => automatic
    [created] => 1618927807
    [currency] => usd
    [customer] => 
    [description] => 
    [invoice] => 
    [last_payment_error] => 
    [livemode] => 
    [metadata] => Stripe\StripeObject Object
        (
        )

    [next_action] => 
    [on_behalf_of] => 
    [payment_method] => pm_1IiKHlLkXRKe1IYhXbwwZjVj
    [payment_method_options] => Stripe\StripeObject Object
        (
            [card] => Stripe\StripeObject Object
                (
                    [installments] => 
                    [network] => 
                    [request_three_d_secure] => automatic
                )

        )

    [payment_method_types] => Array
        (
            [0] => card
        )

    [receipt_email] => 
    [review] => 
    [setup_future_usage] => 
    [shipping] => 
    [source] => 
    [statement_descriptor] => 
    [statement_descriptor_suffix] => 
    [status] => succeeded
    [transfer_data] => 
    [transfer_group] => 
)

