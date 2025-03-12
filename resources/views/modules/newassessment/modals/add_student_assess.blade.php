<div class="modal fade" id="add-assess-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Student Assessment</h5>
                <button class="close" type="" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
            <form method="POST" action="{{ route('assessments.store') }}">
    @csrf
    <input type="hidden" name="student_no" value="{{ $student_no }}">
    <input type="hidden" name="subcode" value="{{ $subcode }}">
    <input type="number" name="paper1" placeholder="Paper 1 Score">
    <input type="number" name="paper2" placeholder="Paper 2 Score">
    <button type="submit">Save</button>
</form>
            </div>
        </div>
    </div>
</div>
   
