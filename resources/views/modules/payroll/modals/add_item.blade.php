<div class="modal fade" id="add-item-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white" id="exampleModalLabel">Add Payroll Item</h5>
                <a href="#"><span class="close text-white" data-dismiss="modal" aria-label="Close"
                        aria-hidden="false">×</span></a>
            </div>
            <div class="modal-body">
                <form id="add-item-form">
                    @csrf
                    <div class="form-group">
                        <label for="">Description</label>
                        <input type="text" name="desc" placeholder="Enter payroll item description eg.SSNIT"
                            class="form-control" required>
                    </div>
                    <div class="form-group mt-2">
                        <label for="">Type</label>
                        <select type="text" id="add-item-type" name="type" class="form-control select2" required>
                            <option value="">--Select--</option>
                            <option value="ALL">Earnings</option>
                            <option value="DUD">Deductions</option>
                            <option value="CON">Contributions</option>
                            <option value="PEN">Pension</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light btn-sm rounded" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-light btn-sm rounded" form="add-item-form" type="reset">Reset</button>
                <button class="btn btn-primary btn-sm rounded" form="add-item-form" type="submit" name="submit"> <i
                        class=""></i> Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
    var itemForm = document.getElementById("add-item-form");

    $(itemForm).submit(function(e) {
        e.preventDefault();

        var formdata = new FormData(itemForm);
        formdata.append("createuser", createuser);
        formdata.append("school_code", school_code);

        Swal.fire({
            title: 'Are you sure you want to add item?',
            text: "Or click cancel to abort!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Submit'
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    title: "Adding please wait...",
                    didOpen: () => Swal.showLoading(),
                    showConfirmButton: false,
                    allowEscapeKey: false,
                    allowOutsideClick: false
                });

                fetch(`${appUrl}/staff/add_payroll_item`, {
                        method: "POST",
                        body: formdata,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.ok) {
                            Swal.fire({
                                text: data.msg || "Something went wrong",
                                icon: "error"
                            });
                            return;
                        }

                        Swal.fire({
                            text: "Payroll item added successfully",
                            icon: "success"
                        });

                        $("#add-item-modal").modal('hide');
                        $("select").val(null).trigger('change');
                        payrollItemTable.ajax.reload(null, false);
                        itemForm.reset();
                    })
                    .catch(err => {
                        console.error("Add payroll item error:", err);
                        Swal.fire({
                            text: "Adding payroll item failed. See console for details.",
                            icon: "error"
                        });
                    });
            }
        });
    });
</script>
