<!DOCTYPE html>
<html>
<head>
    <title>Create Contractor</title>
</head>
<body>

<h1>Create Contractor</h1>

<form action="{{ route('contractors.store') }}" method="POST">

    @csrf

    <div>
        <label>Contractor Name</label><br>
        <input type="text" name="contractor_name">
    </div>

    <br>

    <div>
        <label>Mobile</label><br>
        <input type="text" name="mobile">
    </div>

    <br>

    <div>
        <label>Work Category</label><br>
        <input type="text" name="work_category">
    </div>

    <br>

    <button type="submit">
        Save Contractor
    </button>

</form>

</body>
</html>