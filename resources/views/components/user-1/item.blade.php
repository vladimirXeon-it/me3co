<div class="form-group row">
    <div class="col-sm-5">
        <input type="text" class="form-control" name="items[${i}][item]" placeholder="Item">
    </div>
    <div class="col-sm-5">
        <input type="number" step="0.001" class="form-control" name="items[${i}][cost]" placeholder="$">
    </div>
    <div class="col-sm-2">
        <button type="button" class="btn btn-danger item-remove">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>
