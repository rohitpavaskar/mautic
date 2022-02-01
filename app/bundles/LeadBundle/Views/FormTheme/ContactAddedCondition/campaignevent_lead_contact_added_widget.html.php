<?php

/*
 * @copyright   2022 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<div class="row condition-row">
    <div class="col-xs-4">
        <?php echo $view['form']->row($form['timestamp']); ?>
    </div>
    <div class="col-xs-4">
        <?php echo $view['form']->row($form['operator']); ?>
    </div>
    <div class="col-xs-4">
        <div class="row">
            <div class="col-sm-4">
                <?php echo $view['form']->row($form['triggerInterval']); ?>
            </div>
            <div class="col-sm-8">
                <?php echo $view['form']->row($form['triggerIntervalUnit']); ?>
            </div>
        </div>
    </div>
</div>
