<div class="modal fade" id="send-staff-message-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-comment mr-1"></i>Send Message</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row shadow-sm border border-default p-2 rounded mx-1 mb-3">
                    <!-- Notification types -->
                    <div class="col">
                        <h6 class="border-bottom">Select Notification Type</h6>
                        <!-- SMS notification -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" selected type="radio" id="sms-notification-type" required
                                form="send-message-form" name="notificationType" value="sms">
                            <label class="form-check-label" for="sms-notification-type">SMS</label>
                        </div>

                        <!-- Push notification -->
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="push-notification-type"
                                title="Send push notifications" disabled required form="send-message-form"
                                name="notificationType" value="push">
                            <label class="form-check-label" for="push-notification-type">Push
                                Notification</label>
                        </div>
                    </div>
                </div>

                <!-- Notification content -->
                <div class="row m-1 ">
                    <div class="col mx-auto p-3 border border-default rounded">
                        {{-- <h6 class="border-bottom">Notification Content</h6>
                        <br> --}}
                        <form id="send-message-form">
                            <!-- Message Recipient -->
                            <input type="text" name="staffPhone" hidden id="message-recipient-phone">
                            <input type="text" name="staffCode" hidden id="message-recipient-code">
                            <!-- Notification title -->
                            <div class="form-group" id="notification-title-holder" hidden>
                                <label for="push-notification-title">Push notification title:</label>
                                <input placeholder="eg. Upcoming free health screening" type="text" class="form-control"
                                    name="notificationTitle" id="push-notification-title">
                            </div>

                            <!-- Notification body -->
                            <div class="form-group">
                                <label for="notification-body">Message:</label>
                                <textarea required placeholder="Enter the content of the message here" rows="8"
                                    col="5" class="form-control" name="notificationBody"></textarea>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Notification content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                <button type="reset" form="send-message-form" class="btn btn-light btn-sm">Reset</button>
                <button type="submit" form="send-message-form" name="submit"
                    class="btn btn-primary btn-sm">Send</button>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        let notificationForm = document.forms["send-message-form"];
        let notificationTitleHolder = document.getElementById("notification-title-holder");

        notificationForm.notificationType.forEach(type => {
            type.addEventListener("change", function (e) {
                switch (type.value) {
                    case "push":
                        notificationTitleHolder.hidden = false;
                        notificationForm.notificationTitle.hidden = false;
                        notificationForm.notificationTitle.required = true;
                        break;
                    default:
                        notificationTitleHolder.hidden = true;
                        notificationForm.notificationTitle.hidden = true;
                        notificationForm.notificationTitle.required = false;
                        break;
                }
            });
        });

        notificationForm.addEventListener("submit", function (e) {
            e.preventDefault();
            let formdata = new FormData();
            formdata.append("messageType", this.notificationType.value);
            formdata.append("messageBody", this.notificationBody.value);
            formdata.append("messageTitle", this.notificationTitle.value);
            formdata.append("staffCode", this.staffCode.value);
            formdata.append("staffPhone", this.staffPhone.value);
            formdata.append("createuser", `${createuser}`);
            formdata.append("school_code", `${school_code}`);

            fetch(`${appUrl}/api/staff/send_message`, {
                    method: "POST",
                    body: formdata,
                    headers: {
                        "Authorization": "d16xA0oqWRi2barEd1Ru3JVM3uveym6nw2ntVsfSUl0kf8T5XNVhSykpoqswweeJI7OjiYTc1rtkDTKE",
                    }
                }).then(res => res.json())
                .then(data => {

                })

            Swal.fire({
                title: "",
                text: "The sms will be delivered to this staff",
                timer: 2800,
                icon: "success",
                showConfirmButton: false,
            });
            notificationForm.reset();
            $("#send-staff-message-modal").modal('hide');
        });
    })();

</script>
