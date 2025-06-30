<div class="modal fade" id="payroll-file-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title">Upload Staff Payroll Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="payroll-import-form" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12" id="load-feeds"></div>
                    </div>
                    <div class="form-group">
                        <label for="">Year <span class="text-danger">*</span></label>
                        <select name="year" class="form-control select2" required>
                            <option value="" class="text-gray-100">-- Select --</option>
                            @foreach ($acyear as $item)
                                <option value="{{ $item->acyear_code }}">{{ $item->acyear_desc }}
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="">Month <span class="text-danger">*</span></label>
                        <select name="month" class="form-control select2" required>
                            <option value="" class="text-gray-100">-- Select --</option>
                            <option value="JAN">January</option>
                            <option value="FEB">February</option>
                            <option value="MAR">March</option>
                            <option value="APR">April</option>
                            <option value="MAY">May</option>
                            <option value="JUN">June</option>
                            <option value="JULY">July</option>
                            <option value="AUG">August</option>
                            <option value="SEP">September</option>
                            <option value="OCT">October</option>
                            <option value="NOV">November</option>
                            <option value="DEC">December</option>
                        </select>
                    </div>

                    <div class="col mt-3">
                        <label for="">Choose Excel File <span class="text-danger">*</span></label>
                        <input type="file" accept=".xlsx,.xls" name="file" id="csv" class="form-control"
                            required>
                    </div>

                    <br>
                    {{-- <a class="btn btn-warning" href="{{ route('export') }}">Export User Data</a> --}}
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                {{-- <button type="reset" form="payroll-import-form" class="btn btn-light btn-sm">Reset</button> --}}
                <button type="submit" form="payroll-import-form" class="btn btn-primary btn-sm">Upload</button>
            </div>
        </div>
    </div>
</div>

<script>
    var payrollImportForm = document.getElementById("payroll-import-form");
    // var spin = document.getElementById("spin");
    const loadFeed = document.getElementById("load-feeds");

    $(payrollImportForm).submit(function(e) {
        e.preventDefault();

        // spin.style.display = "block";
        // loadFeed.innerHTML =
        //     "<p class='alert alert-primary p-1'>Processing</p>";

        Swal.fire({
            text: "Importing...",
            showConfirmButton: false,
            allowEscapeKey: false,
            allowOutsideClick: false
        })
        var formdata = new FormData(payrollImportForm)
        formdata.append("createuser", createuser);
        formdata.append("school_code", school_code);


        fetch(`${appUrl}/import-payroll`, {
            method: "POST",
            body: formdata,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'laravel_session': $('meta[name="laravel_session"]').attr(
                    'content'),
                'Accept': 'application/json'
            }
        }).then(function(res) {
            return res.json();

        }).then(function(data) {
            // spin.style.display = "none";

            if (!data.ok) {
                Swal.fire({
                    text: data.error.failure + `Error found on row ${data.error.row}`,
                    type: "info"
                });
                return;
            }

            Swal.fire({
                text: "Payroll data import successful",
                type: "success",
            })
            $("#add-payroll-modal").modal('hide');
            $("select").val(null).trigger('change');
            payrollTable.ajax.reload(null, false);

            setTimeout(() => {
                loadFeed.innerHTML = null;
            }, 2000);
            setTimeout(() => {
                $("#payroll-file-modal").modal('hide');
            }, 1000);
            payrollImportForm.reset();
        }).catch(function(err) {
            if (err) {
                Swal.fire({
                    text: "Importing failed",
                    type: "error"
                });
            }
        })
    });
</script>
