

<?php
// echo "<pre>";
// print_r($data);
// echo "</pre>";

?>

<table class="table table-condensed">
    <tr>
        <th>NO</th>
        <th>TANGGAL</th>
        <th>NOMINAL</th>
    </tr>
    <?php foreach ($data as $key_laba_rugi => $value_laba_rugi): ?>
        <tr>
            <td><?=$key_laba_rugi+1 ?></td>
            <td><?=$value_laba_rugi->tanggal ?></td>
            <td><?=$value_laba_rugi->nominal ?></td>
        </tr>
    <?php endforeach ?>

</table>


@section('script')