<?php if (empty($list)): ?>
<h2>Empty list</h2>
<?php else: ?>
<table border="1">
    <thead>
        <th>Age</th>
        <th>User ids</th>
        <th>User names</th>
    </thead>
    <tbody>
        <?php foreach ($list as $item): ?>
            <tr>
                <td><?= $item->age ?></td>
                <td><?= $item->user_ids ?></td>
                <td><?= $item->user_names ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif ?>
