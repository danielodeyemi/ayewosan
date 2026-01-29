<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice</title>
    <style>
        .invoice-box {
            margin: auto;
            padding: 20px;
            font-size: 16px;
            line-height: 20px;
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
            padding-bottom: 10px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 5px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 10px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: 1px solid #eee;
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

            .invoice-box tr.topinfodesign {
                border-bottom: 1px solid #ddd;
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
                                <img src="https://topiclaboratory.com/uploads/app_image/logo-small.png" style="width: 90%; max-width: 300px" />
                            </td>

                            <td>
                                Test Date : {{ \Carbon\Carbon::parse($labTestsResults->result_date)->format('Y-m-d') }}<br />
                                Delivery Date: {{ \Carbon\Carbon::parse($labTestsResults->delivery_date_time)->format('Y-m-d') }}<br />
                                RESULTS OF LABORATORY INVESTIGATIONS
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="3">
                    <table>
                        <tr class="topinfodesign">
                            <td>
                                <strong>Patient Details:</strong> <br />
                                Name: {{ $labTestsResults->bills->patient->name }} <br />
                                Sex: {{ $labTestsResults->bills->patient->gender }} | Age: {{ \Carbon\Carbon::parse($labTestsResults->bills->patient->birth_date)->age }}<br />
                                Phone: {{ $labTestsResults->bills->patient->phone_number }}
                            </td>

                            <td>
                                <strong>Laboratory Details:</strong> <br />
                                Topic Diagnostic Laboratory<br />
                                10 Cinema Road, Sagamu, Ogun State<br />
                                09030856589
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <hr>

        {!! $labTestsResults->result_content !!}

        <table>
            <tr class="information">
                <td colspan="3">
                    <table>
                        <tr>
                            <td>
                                <strong>Extra Information</strong> <br />
                                Laboratory Scientist: {{ $labTestsResults->performed_by }}<br />
                                Remarks: {{ $labTestsResults->report_remarks }}<br />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>
    </div>
</body>

</html>