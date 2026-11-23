<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 *
 * QScms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * QScms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with QScms. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * QsCms 项目文件
 *
 * @Project      QsCms
 * @Author       Qiaoshuo 乔硕 (isher，isherqiao) <qiaoshuo@gmail.com>
 * @Site         https://qsis.cn
 * @File         set_github.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  GitHub Pages sync settings: repository, branch, token and the local sync directory.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

// set_github.php - GitHub Pages同步配置（样式和set_ssg.php完全一致）

$github = $qs_adv_config['github'] ?? 'close';

?>
<div class="small_form small_form2">
<b><?php echo L('GitHub_Pages'); ?></b>

    <?php if ($github === 'open'): ?>
    
<!-- 同步按钮：和SSG的Generator按钮样式一致 -->
<b><a href="github_sync.php" target="_blank" class="button"  name=""  value="" style="padding:10px 20px; font-size:16px; cursor:pointer;" />Sync to GitHub Pages</a></b>
	
<b><a href="github_cron.php" target="_blank" class="button"  name=""  value="" style="padding:10px 20px; font-size:16px; cursor:pointer;" />crontab to GitHub Pages</a></b>

<b><a href="githubdel.php" target="_blank" class="button"  name=""  value="" style="padding:10px 20px; font-size:16px; cursor:pointer;" />DEL to GitHub Pages</a></b>

<div class="small_form small_form2">
<div class="clear"></div>

<!-- GitHub Token 配置项 -->
<div class="field">
<div class="label">
<?php echo L('github_token'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('GitHub_Token_Placeholder'); ?>" type="text" name="github_token" value="" />
</div>
<div class="info">
<?php echo L('github_token_tip'); ?><?php echo L('GitHub_Token_Gen_Url_Tip'); ?>
</div>
<div class="clear"></div>
</div>

<!-- GitHub 用户名/组织名 -->
<div class="field">
<div class="label">
<?php echo L('github_owner'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('GitHub_Owner_Placeholder'); ?>" type="text" name="github_owner" value="<?php echo htmlspecialchars($github_owner ?? ''); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- GitHub 仓库名 -->
<div class="field">
<div class="label">
<?php echo L('github_repo'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('GitHub_Repo_Placeholder'); ?>" type="text" name="github_repo" value="<?php echo htmlspecialchars($github_repo ?? ''); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<!-- GitHub 分支名 -->
<div class="field">
<div class="label">
<?php echo L('github_branch'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('GitHub_Default_Branch_Placeholder'); ?>" type="text" name="github_branch" value="<?php echo htmlspecialchars($github_branch ?? 'master'); ?>" />
</div>
<div class="info">
<?php echo L('github_branch_tip'); ?><?php echo L('GitHub_Pages_Default_Branch_Tip'); ?>
</div>
<div class="clear"></div>
</div>

<!-- 同步目录（复用SSG的output目录） -->
<div class="field">
<div class="label">
<?php echo L('sync_directory'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('Sync_Dir_Placeholder'); ?>" type="text" name="github_sync_dir" value="<?php echo htmlspecialchars($github_sync_dir ?? $static_output ?? '../static_site'); ?>" />
</div>
<div class="info">
<?php echo L('sync_directory_tip'); ?><?php echo L('Sync_Dir_Auto_Reuse_Tip'); ?>
</div>
<div class="clear"></div>
</div>

<!-- 提交说明 -->
<div class="field">
<div class="label">
<?php echo L('commit_message'); ?>
</div>
<div class="price">
<input class="input" placeholder="<?php echo L('Commit_Msg_Placeholder'); ?>" type="text" name="github_commit_msg" value="<?php echo htmlspecialchars($github_commit_msg ?? 'Auto sync from QSIS SSG'); ?>" />
</div>
<div class="info"></div>
<div class="clear"></div>
</div>

<div class="clear"></div>
</div>

    <?php else: ?>
    <div class="field">
        <div class="label"></div>
        <div class="price">
            <span style="color:#999;"><?= L('GitHub_Pages_Rsync_Not_Enabled') ?></span>
        </div>
        <div class="info"></div>
        <div class="clear"></div>
    </div>
    <?php endif; ?>
</div>
