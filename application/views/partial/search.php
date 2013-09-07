<?= Form::open(NULL, array('name' => 'search')) ?>
  <p style="text-align: center;">
    <b>Search:</b>
    <?= Form::input('searchOn', $searchOn, array('id' => 'searchOn', 'size' => 30)) ?>
    <?= Form::submit("search", "Search") ?>
    <?= Form::submit("reset", "Reset") ?>
  </p>
<?= Form::close() ?>
