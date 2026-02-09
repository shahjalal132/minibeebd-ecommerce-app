<div class="modal-dialog">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel">Payment Method Update</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('admin.payment-methods.update',[$item->id]) }}" method="POST" id="ajax_form">
      @csrf
      {{ method_field('PATCH') }}
      <div class="modal-body">
          <div class="form-group mb-3">
              <label class="form-label">Method Name</label>
              <input type="text" class="form-control" name="name" value="{{$item->name}}" required>
          </div>
          
        <div class="mb-3">
            <label  class="form-label">Number</label>
            <input type="text" name="number" class="form-control" value="{{$item->number}}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Instruction</label>
            <textarea name="instruction" class="form-control">{{$item->instruction}}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Type (Optional)</label>
            <select name="type" class="form-control">
                <option value="">Select Type</option>
                <option value="Personal" {{$item->type == 'Personal' ? 'selected' : ''}}>Personal</option>
                <option value="Agent" {{$item->type == 'Agent' ? 'selected' : ''}}>Agent</option>
            </select>
        </div>
        
        <div class="mb-3">
            <div class="form-check">
              <label class="form-check-label">
                <input type="checkbox" name="status" class="form-check-input" value="1" {{$item->status =='1' ?'checked':''}}>Active
              </label>
            </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary"> Update</button>
      </div>
    </form>
  </div>
</div>
