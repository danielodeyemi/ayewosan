<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice</title>
    <style>
        /* Add your CSS styles here */
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #333;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
            text-align: right;
        }

        .invoice-box table tr.total td:nth-child(3) {
            text-align: right;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="3">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(resource_path('images/ayewosan_laboratory_logo.png'))) }}" style="width: 90%; max-width: 300px" />
                            </td>

                            <td>
                                Bill #: {{ $bills->id }}<br />
                                Created: {{ $bills->bill_date }}<br />
                                Bill Status: {{ $bills->payment_status }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="3">
                    <table>
                        <tr>
                            <td>
                                <strong>Patient Details:</strong> <br />
                                Name: {{ $bills->patient->name }}<br />
                                Sex: {{ $bills->patient->gender }}<br />
                                Phone: {{ $bills->patient->phone_number }}
                            </td>

                            <td>
                                <strong>Laboratory Details:</strong> <br />
                                Àyewòsàn Laboratory Management System<br />
                                Developed by Daniel Odeyemi<br />
                                0701-234-5678
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>S/N</td>

                <td style="text-align: left;">Test</td>

                <td style="text-align: right;">Price</td>
            </tr>

            @foreach ($bills->labTests as $labTest)
            <tr class="item">
                <td>{{ $loop->iteration }}</td>
                <td style="text-align: left;">{{ $labTest->name }}</td>
                <td style="text-align: right;">NGN {{ $labTest->patient_price }}</td>
            </tr>
            @endforeach

            <tr class="total">
                <td></td>
                <td>Sub Total (NGN): </td>
                <td>Not decided</td>

            </tr>
            <tr class="total">
                <td></td>
                <td>Discount (NGN): </td>
                <td>{{ $bills->discount }}</td>
            </tr>

            <tr class="total">
                <td></td>
                <td>Total Payable (NGN):</td>
                <td>{{ $bills->total_amount }}</td>
            </tr>

            <tr class="total">
                <td></td>
                <td>Paid Amount (NGN): </td>
                <td>{{ $bills->paid_amount }}</td>
            </tr>

            <tr class="total">
                <td></td>
                <td>Due Amount (NGN): </td>
                <td>{{ $bills->due_amount }}</td>
            </tr>

            <tr class="information">
                <td colspan="3">
                    <table>
                        <tr>
                            <td>
                                <strong>Notes</strong> <br />
                                Prepared by: {{ $bills->patient->name }}<br />
                                Remarks: {{ $bills->remarks }}<br />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>
    </div>
</body>
</html>

<!-- The values below have been checked for correct value returns-->
<!-- <body>
    <h1>Invoice</h1>
    <p>Bill Number: {{ $bills->id }}</p>
    <p>Bill Date: {{ $bills->bill_date }}</p>
    <p>Patient Name: {{ $bills->patient->name }}</p>
    <p>Patient Gender: {{ $bills->patient->gender }}</p>
    <p>Patient Phone Number: {{ $bills->patient->phone_number }}</p>
    <p>Patient Email: {{ $bills->patient->patient_email }}</p>
    <p>Patient ID: {{ $bills->patient_id }}</p>
    <p>Total Amount: {{ $bills->total_amount }}</p>
    <p>Discount: {{ $bills->discount }}</p>
    <p>Paid Amount: {{ $bills->paid_amount }}</p>
    <p>Due Amount: {{ $bills->due_amount }}</p>
    <p>Payment Status: {{ $bills->payment_status }}</p>
    <p>Remarks: {{ $bills->remarks }}</p>
    <p>Lab Tests:
       // @foreach ($bills->labTests as $labTest)
       // {{ $labTest->name }},
       // @endforeach
    </p>
    <p>Referring User: {{ $bills->patient->referringUser->name }}</p>
    <p>Processed By: {{ $bills->processedBy->name }}</p>
</body> -->
<!-- End value check -->