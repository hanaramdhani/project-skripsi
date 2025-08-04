

<?php
// echo "<pre>";
// print_r($data);
// echo "</pre>";

?>

<table class="table table-condensed">
    <tr>
        <th>NO</th>
        <th>URAIAN</th>
        <th>NOMINAL</th>
    </tr>
    <?php foreach ($data as $key_laba_rugi => $value_laba_rugi): ?>
        <tr>
            <td><?=$value_laba_rugi->no ?></td>
            <td><?=$value_laba_rugi->uraian ?></td>
            <td><?=$value_laba_rugi->nominal ?></td>
        </tr>
    <?php endforeach ?>

</table>


@section('script')