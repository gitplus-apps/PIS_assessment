<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">


        <title>Staff Payslip</title>
        {{-- Css  --}}
        {{-- <link href="{{ asset('css/icon.jpeg') }}" rel="shortcut icon" type="image/jpeg" /> --}}

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,500;0,700;1,100;1,300;1,400;1,500&display=swap"
            rel="stylesheet">
        <script type="text/javascript" src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <style type="text/tailwindcss">
        table{
        @apply w-full text-left table-fixed;
        }

        th, td {
        @apply py-1;
        }

        td {
    word-break: break-word;
    white-space: normal;
}
        </style>
        <style>
@page {
    size: A4 portrait;
    margin: 20mm; /* Adjust as needed */
}

@media print {
    body {
        -webkit-print-color-adjust: exact !important; /* Ensures background colors print */
        print-color-adjust: exact !important;
        font-size: 11pt;
        margin: 0;
        padding: 0;
        width: 100%;
    }

    html, body, section {
        width: 100%;
        height: auto;
    }

    .no-print {
        display: none !important;
    }

    table {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    * {
        box-shadow: none !important;
    }
}
</style>

    </head>


    <body class="flex items-center justify-center w-full h-full">
        <section class="p-6 max-w-[794px] bg-white">
            <div class="flex w-full justify-between items-center">
                <h4 class="text-lg font-bold">Payslip For: {{$staff_name}}</h4>
                <div class="w-16 h-16 overflow-hidden">
                    <img src="{{ asset('PHO.jpg') }}" alt="" class="w-full h-full object-cover">
                </div>
            </div>

            <section class="w-full flex flex-row gap-3 my-6">
                <div>
                    <span class="font-bold">Personal Details</span>
                    <table class="w-full">
                        <tbody>
                            <tr>
                                <td>Tel:</td>
                                <td>{{$staff->phone ?? 'N/A'}}</td>
                            </tr>
                            <tr>
                                <td>Email: </td>
                                <td>{{strtolower($staff->email) ?? 'N/A'}}</td>
                            </tr>
                            <tr>
                                <td>Social Security No: </td>
                                <td>{{isset($staff->ssnit) === 0 ? $staff->ssnit : 'N/A'}}</td>
                            </tr>
                            <tr>
                                <td>Ghana Card: </td>
                                <td>{{$staff->national_id_no ?? 'N/A'}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <span class="font-bold">Employment Details</span>
                    <table class="w-full">
                        <tbody>
                            <tr>
                                <td>Staff ID:</td>
                                <td>{{$staff->staffno ?? 'N/A'}}</td>
                            </tr>
                            <tr>
                                <td>Department: </td>
                                <!-- <td>{{$job_position ?? 'N/A'}}</td> -->
                                <td>N/A</td>
                            </tr>
                            <tr>
                                <td>Position: </td>
                                <td>{{$job_position ?? 'N/A'}}</td>
                            </tr>
                            <tr>
                                <td>Location: </td>
                                <td>{{$school}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-right w-full">
                    <span class="font-bold">Paid by: {{$school}}</span>
                    <p>
                        GPS CQ 0343 4521
                        +233244522005
                        perculiarschool@gmail.com
                    </p>
                </div>
            </section>
            <!-- Period -->
            <section class="my-6 flex flex-row items-center justify-between border-y-2 border-y-black py-4 bg-slate-50">
                <span><strong>Pay Month:</strong> {{$month}}</span>
                <span><strong>Payment Date:</strong> {{ date("d-M-Y",strtotime($payment_date)) }}</span>
                <span><strong>Net Pay:</strong>  {{ number_format($net,2) }} </span>
            </section>

            <!-- Earnings and Deductions -->
            <section class="w-full flex w-full flex-row gap-6 justify-between">
                <!-- Earnings -->
                <div class="py-2 flex-[0_0_60%]">
                    <table class="mb-5">
                        <thead>
                            <tr>
                                <th class="border-b border-b-black">Earnings</th>
                                <th class="border-b border-b-black"></th>
                                <th class="border-b border-b-black text-right">This Period (GH¢)</th>
                                <th class="border-b border-b-black text-right">YTD (GH¢)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Base Salary</td>
                                <td></td>
                                <td class="text-right">{{$slips[0]['earn']}}</td>
                                <td class="text-right">{{$slips[0]['earn']}}</td>
                            </tr>
                            <tr class="font-bold">
                                <td></td>
                                <td class="border-y-2 border-y-black bg-slate-50">Gross Pay</td>
                                <td class="border-y-2 border-y-black bg-slate-50 text-right">{{$gross}}</td>
                                <td class="border-y-2 border-y-black bg-slate-50 text-right">{{$gross}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table class="mb-5">
                        <thead>
                            <tr>
                                <th class="border-b border-b-black">Deductions</th>
                                <th class="border-b border-b-black">Stationary</th>
                                <th class="border-b border-b-black"></th>
                                <th class="border-b border-b-black"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td>Tax</td>
                                <td class="text-right">{{$slips[3]['deduct']}}</td>
                                <td class="text-right">{{$slips[3]['deduct']}}</td>
                            </tr>
                            <tr class="">
                                <td></td>
                                <td class="font-bold border-b border-b-black">Other Deductions</td>
                                <td class="border-b border-b-black"></td>
                                <td class="border-b border-b-black"></td>
                            </tr>
                            <tr class="">
                                <td></td>
                                <td class="">Welfare Fund (30%)</td>
                                <td class="text-right">30</td>
                                <td class="text-right">30</td>
                            </tr>
                            <tr class="font-bold">
                                <td></td>
                                <td class=" border-y-2 border-y-black bg-slate-50">Net Pay</td>
                                <td class="border-y-2 border-y-black bg-slate-50 text-right">{{$net}}</td>
                                <td class="border-y-2 border-y-black bg-slate-50 text-right">{{$net}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pension -->
                <div class="flex-[0_0_38%] space-y-6">
                    <!-- Pension Table -->
                    <table>
                        <thead class="w-full">
                            <tr class="w-full">
                                <th class="border-b border-b-black">Pension</th>
                                <th class="border-b border-b-black text-right">This Period (GH¢)</th>
                                <th class="border-b border-b-black text-right">YTD (GH¢)</th>
                            </tr>
                        </thead>
                        <tbody class="w-full">
                            <tr>
                                <td>Tier 2</td>
                                <td class="text-right">{{$slips[4]['deduct']}}</td>
                                <td class="text-right">{{$slips[4]['deduct']}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <table>
                        <thead class="w-full">
                            <tr class="w-full">
                                <th class="border-b border-b-black">Tax Breakdown</th>
                                <th class="border-b border-b-black text-right"></th>
                                <th class="border-b border-b-black text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="w-full">
                            <tr>
                                <td>Registrar PAYE</td>
                                <td class="text-right">{{$slips[3]['deduct']}}</td>
                                <td class="text-right"></td>
                            </tr>
                        </tbody>
                    </table>
                    <table>
                        <thead class="w-full">
                            <tr class="w-full">
                                <th class="border-b border-b-black">Deductuctible Tax Reliefs</th>
                                <th class="border-b border-b-black text-right"></th>
                                <th class="border-b border-b-black text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="w-full">
                            <tr>
                                <td>No reliefs awarded</td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                            </tr>
                        </tbody>
                    </table>

                    <table>
                        <thead class="w-full">
                            <tr class="w-full">
                                <th class="border-b border-b-black">Important Notes</th>
                                <th class="border-b border-b-black text-right"></th>
                                <th class="border-b border-b-black text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="w-full">
                            <tr>
                                <td class="w-full"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- Payment Details -->
            <section>
                <div class="font-bold flex flex-row w-full justify-between items-center mt-6 py-3 border-b-2 border-b-black">
                    <h4>Payment Details</h4>
                    <h4>Amount</h4>
                </div>
                <div class="flex flex-row items-center justify-between py-3">
                    <div class="flex flex-row items-center justify-center">
                        <div class="w-32 h-4"></div>
                        <span>Cash or Cheque</span>
                    </div>
                    <span class="font-bold">GH¢ {{$net}}</span>
                </div>
            </section>
        </section>


        <script>
        document.getElementById('button').addEventListener("click", function() {
            window.print();
        })
        </script>
    </body>

</html>
