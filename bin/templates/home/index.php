

<?= $user->usernames->getQuery()->addRestriction('expires', null, 'IS')->fetch()->name ?>
<?= $user->attributes->getQuery()->count() ?>