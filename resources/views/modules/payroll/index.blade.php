@component('layout.app')


@section('page-name', 'Payroll')
@section('page-content')


    <div class="container-fluid mt-5">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800"><span class="fas fa-fw fa-file"></span> Payroll</h1>
            {{-- <div>
                <a href="#" data-toggle="modal" data-target="#add-payroll-modal" data-toggle="tooltip"
                    data-placement="bottom" title="Add payroll" class="btn btn-sm rounded btn-primary shadow-sm">Add
                    payroll</a>

            </div> --}}
        </div>
        <!-- Content Column -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="payroll-tab" data-toggle="tab" href="#payroll" role="tab"
                    aria-controls="contact" aria-selected="false"> Payroll</a>
            </li>

            {{-- <li class="nav-item">
                <a class="nav-link" id="payroll-tab" data-toggle="tab" href="#payroll" role="tab"
                    aria-controls="contact" aria-selected="false"> Staff Salary</a>
            </li> --}}

        </ul>

        <div class="tab-content" id="myTabContent">


            <!--Payroll Table-->
            <div class="tab-pane fade show active" id="payroll" role="tabpanel" aria-labelledby="payroll-tab">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table width="100%"
                                class="table table-bordered table-sm table-striped table-hover dataTable js-exportable"
                                id="payroll-table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Staff</th>
                                        <th>Pay Month</th>
                                        <th>Pay Year</th>
                                        <th>Net Salary</th>
                                        <th>Date Recorded</th>
                                        <th>Action </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data is fetched here using ajax -->
                                </tbody>
                            </table>
                        </div>
                        <!-- End of my non-teaching datatable -->
                    </div>
                </div>
            </div>

            {{-- Class teacher tab --}}
            <div class="tab-pane fade mt-3" id="class-teacher" role="tabpanel" aria-labelledby="teacher-tab">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                    </div>
                    <div class="card-body">
                                                <!-- End class datatable -->
                    </div>
                </div>
            </div>
            {{-- End Of Class teacher tab --}}

        </div>
        <!-- DataTales -->

    </div>



    @include('modules.payroll.modals.view_payroll')
    @include('modules.payroll.modals.view_staff_payroll')
    <script>
        //Payroll table
        const schoolCode = @json(Auth::user()->school_code);

        var payrollTable = $('#payroll-table').DataTable({
            "lengthChange": false,
            dom: 'Bfrtip',
            ajax: {
                url: `${appUrl}/staff/payroll/${schoolCode}`,
                type: "GET",
                headers: {
                    'XSRF-TOKEN': $('meta[name="xsrf-token"]').attr('content'),
                    'laravel_session': $('meta[name="laravel_session"]').attr(
                        'content'),
                    'Accept': 'application/json'
                }
            },
            order: [
                [4, "desc"]
            ],
            processing: true,
            columns: [{
                    data: "staff_name"
                },
                {
                    data: "month"
                },

                {
                    data: "year"
                },
                {
                    data: "net"
                },
                {
                    data: 'date'
                },
                {
                    data: "action"
                }
            ],
            buttons: [{
                    extend: 'print',
                    title: `${loggedInUserSchoolName} - payroll List`,
                    attr: {
                        class: "btn btn-sm btn-info rounded-right"
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                },
                {
                    extend: 'copy',
                    title: `${loggedInUserSchoolName} - payroll List`,
                    attr: {
                        class: "btn btn-sm btn-info rounded-right"
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                },
                {
                    extend: 'excel',
                    title: `${loggedInUserSchoolName} - payroll List`,
                    attr: {
                        class: "btn btn-sm btn-info rounded-right"
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                },
                {
                    extend: 'pdf',
                    title: `${loggedInUserSchoolName} - payroll List`,
                    attr: {
                        class: "btn btn-sm btn-info rounded-right"
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                },
                {
                    text: "Refresh",
                    attr: {
                        class: "ml-2 btn-secondary btn btn-sm rounded"
                    },
                    action: function(e, dt, node, config) {
                        dt.ajax.reload(null, false);
                    }
                },
            ]
        });


        //view table
        $('#view-payroll-table').DataTable({
            "lengthChange": false,
            dom: 'Bfrtip',
            processing: true,
            columns: [{
                    data: "item"
                },
                {
                    data: "amount"
                },
                {
                    data: "action"
                }
            ],
        })

        //View staff payroll items
        $(document).on("click", ".view-btn", function() {
            const data = payrollTable.row($(this).parents("tr")).data();

            $("#view-staff-payroll-modal").modal("show");

fetch(`${appUrl}/get-selected-staff/${data.school_code}/${data.staffno}`,{
        method: 'GET',
        headers: {
            'X-XSRF-TOKEN': $('meta[name="xsrf-token"]').attr('content'),
            'laravel_session': $('meta[name="laravel_session"]').attr('content'),
            'Accept': 'application/json'
        }
    }).then(res=>{
            if(!res.ok) throw new Error("Failed to fetch active staff");
            return res.json();
        }).then(dataResponse=>{
            const {data}=dataResponse;
            $('#view-modal-staff-national_id_no').val(data.national_id_no);
            $('#view-modal-staff-national_id').val(data.national_id);
            $('#view-modal-staff-school_name').val(data.school_name);
            $('#view-modal-staff-position').val(data.job_position);
        });


            // Use the new IDs that match the modal
            $('#view-modal-staff-name').val(data.staff_name);
            $('#view-modal-staff-year').val(data.year);
            $('#view-modal-staff-month').val(data.month);

            const fields = [
                'basic_salary',
                'duty_allowance',
                'food_allowance',
                'hod_increment',
                'gra_paye',
                'ssnit_t2',
                'loan_repayment',
                'fees_payment',
                'land_payment',
                'ssnit_loan'
            ];

            // Use the new ID pattern for all fields
            fields.forEach(field => {
                $(`#view-modal-staff-${field}`).val(data[field] ?? '0.00');
            });

            $('#view-modal-staff-t_earning').val(data.t_earning);
            $('#view-modal-staff-t_deduction').val(data.t_deduction);
            $('#view-modal-staff-net').val(data.net);
        });

        var payrollItemTable = $('#payrol-item-table').DataTable({
            "lengthChange": false,
            dom: 'Bfrtip',
            ajax: {
                url: `${appUrl}/staff/payroll_item/${school_code}`,
                type: "GET",
                headers: {
                    'XSRF-TOKEN': $('meta[name="xsrf-token"]').attr('content'),
                    'laravel_session': $('meta[name="laravel_session"]').attr(
                        'content'),
                    'Accept': 'application/json'
                }
            },
            processing: true,
            columns: [{
                    data: "code"
                },
                {
                    data: "desc"
                },
                {
                    data: "action"
                }
            ],
            buttons: [{
                    extend: 'print',
                    title: `${loggedInUserSchoolName} - payroll List`,
                    attr: {
                        class: "btn btn-sm btn-info rounded-right"
                    },
                    exportOptions: {
                        columns: [0, 1]
                    }
                },
                {
                    extend: 'copy',
                    title: `${loggedInUserSchoolName} - payroll List`,
                    attr: {
                        class: "btn btn-sm btn-info rounded-right"
                    },
                    exportOptions: {
                        columns: [0, 1]
                    }
                },
                {
                    extend: 'excel',
                    title: `${loggedInUserSchoolName} - payroll List`,
                    attr: {
                        class: "btn btn-sm btn-info rounded-right"
                    },
                    exportOptions: {
                        columns: [0, 1]
                    }
                },
                {
                    extend: 'pdf',
                    title: `${loggedInUserSchoolName} - payroll List`,
                    attr: {
                        class: "btn btn-sm btn-info rounded-right"
                    },
                    exportOptions: {
                        columns: [0, 1]
                    }
                },
                {
                    text: "Refresh",
                    attr: {
                        class: "ml-2 btn-secondary btn btn-sm rounded"
                    },
                    action: function(e, dt, node, config) {
                        dt.ajax.reload(null, false);
                    }
                },
                {
                    text: "Add payroll item",
                    attr: {
                        class: "ml-5 btn-primary btn btn-sm rounded"
                    },
                    action: function(e, dt, node, config) {
                        $("#add-item-modal").modal("show");
                    }
                },
            ]
        });


    </script>
@endsection
@endcomponent
