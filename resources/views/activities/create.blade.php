<!DOCTYPE html>
<html>
<head>
    <title>Create Activity</title>
</head>
<body>

<h1>Create Activity</h1>

<form action="{{ route('activities.store') }}" method="POST">

    @csrf

    <div>
        <label>Activity Name</label><br>
        <input type="text" name="activity_name">
    </div>

    <br>

    <div>
        <label>Unit</label><br>
        <input type="text" name="unit">
    </div>

    <br>

    <div>
        <label>Work Stage</label><br>
        <input type="text" name="work_stage">
    </div>

    <br>

    <button type="submit">
        Save Activity
    </button>

</form>

</body>
</html>