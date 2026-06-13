<?php
namespace KoalaForms;

if (!defined('ABSPATH')) {
    exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$users_table = new RegisterUsersTable();
$users_table->prepare_items();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('Users', 'koalaforms'); ?></h1>
    <a href="<?php echo esc_url(admin_url('user-new.php')); ?>" class="page-title-action">
        <?php echo esc_html__('Add New', 'koalaforms'); ?>
    </a>
    <hr class="wp-header-end">

    <form method="get">
        <input type="hidden" name="page" value="koalaforms-users" />
        <?php $users_table->search_box(__('Search Users', 'koalaforms'), 'kf-users'); ?>
        <?php $users_table->display(); ?>
    </form>
</div>
