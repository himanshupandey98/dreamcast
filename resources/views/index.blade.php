<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <!-- IziToast CSS -->
    <link href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css" rel="stylesheet">

    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

    <style>
        .error {
            color: red;
        }
    </style>
</head>

<body>
    <div class="container-fluid m-2">
        <div class="row">

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-8">
                                <h4>Category</h4>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('product.index') }}" class="btn btn-sm btn-success">View Products</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <th>#</th>
                                <th>Name</th>
                                <th>Product Count</th>
                                <th>Action</th>
                            </thead>
                            @foreach($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->products->count() ?? 'N/A'}}</td>
                                <!-- <td><a href="#" class="btn btn-sm btn-success">Edit</a> <a href="#" class="btn btn-sm btn-danger">Delete</a></td> -->
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <h4>{{@$title ? $title : 'Add Category'}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="add-category-form" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="mb-3 col-md-">
                                    <label for="full_name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{old('full_name')}}">
                                    @if($errors->has('name'))   
                                        <span class="text-danger">{{ $errors->first('name') }}</span>
                                    @endif

                                </div>
                            </div>
                            <input type="hidden" name="" id="is_edit" value="0">
                            <button type="submit" class="btn btn-primary" id="submit-btn">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- IziToast JS -->
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

    <script>
        function usersTable() {
            console.log("category listing .....")
            $('.table').DataTable({
                processing: true,
                serverSide: true,
                search: false,
                ajax: "{{ route('category.index') }}", // Server-side route
                columns: [
                    // Serial number column
                    {
                        data: null, // Use null because the data doesn't come directly from the server
                        name: 'count',
                        render: function(data, type, row, meta) {
                            return meta.row + 1; // Row index starts at 0, so add 1
                        }
                    },
                    // Category name column
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row) {
                            return data ? data : 'N/A';
                        }
                    },
                    {
                        data: 'products',
                        name: 'products',
                        render: function(data, type, row) {
                            return data.length ? data.length : "N/A";
                        }
                    },
                    {
                        data: null,
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <a href="{{ url('category') }}/${row.id}/edit" 
                                class="btn btn-sm btn-primary edit-btn">
                                Edit
                                </a>
                                <button onclick="deleteCategory(${row.id})" 
                                        class="btn btn-sm btn-danger">
                                Delete
                                </button>`;
                        }

                    }
                ],
                paging: true,
                pageLength: 10,
                lengthChange: true,
                dom: 'lfrtip',
            });
        }

        $(document).ready(function() {
            $(document).on("click",".edit-btn",function() {
                let attr = $(this).attr('attr')
                $.ajax({
                    url: "{{ route('category.edit', ['category' => '__ID__']) }}".replace('__ID__', attr),
                    dataType: "json",
                    success: function(res) {
                        if (res.status) {
                            $("#name").val(res.data.name)
                            // Updating the hidden input value to edit category
                            $("#is_edit").val(1)
                            $("#add-category-form").attr('action', res.data.action)
                        }
                    }
                })
            })
        })

        // Example Delete Function
        function deleteCategory(id) {
            if (confirm('Are you sure you want to delete this category?')) {
                // Make an AJAX call to delete the category
                $.ajax({
                    url: `/categories/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}' // Include CSRF token for security
                    },
                    success: function(response) {
                        alert('Category deleted successfully!');
                        $('.table').DataTable().ajax.reload(); // Reload DataTable data
                    },
                    error: function(error) {
                        alert('Failed to delete the category. Please try again.');
                    }
                });
            }
        }

        usersTable()
        
        $("#add-category-form").validate({
            rules: {
                name: {
                    required: true
                }
            },
            submitHandler: function(form) {
                let formData = new FormData(form); // Create FormData object from the form
                let isEdit = $("#is_edit").val()
                $.ajax({
                    url: isEdit == 1 ? $("#add-category-form").attr('action') : "{{ route('category.store') }}",
                    data: formData,
                    processData: false, // Don't process the data (jquery will not alter the formData)
                    contentType: false,
                    dataType: "json",
                    type: isEdit == 1 ? "PUT" : "POST",
                    // method: $("#add-category-form").attr("method"),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.status) {
                            iziToast.success({
                                title: 'Success',
                                position: 'topRight',
                                messages: `${res.msg}`
                            })
                            window.location.reload()
                        } else {
                            console.log(res.msg)
                            iziToast.error({
                                title: 'Error',
                                position: 'topRight',
                                messages: `${res.msg}`
                            })
                        }
                    }
                })
            }
        })
    </script>
</body>

</html>