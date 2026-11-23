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
 * @File         tags.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_swbj": tag listing page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 include 'header.php'; ?>

<div class="card">
    <!-- 页面标题 -->
      <div class="container">
    <h1>所有关键词</h1>

    <!-- 标签云核心区域 -->
    <?php if (!empty($all_tags)): ?>
        <div class="tag-cloud" style="
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 20px 0;
        ">
            <?php foreach ($all_tags as $tagName => $posts): ?>
                <?php 
                // 生成标签安全链接
                $safeTag = preg_replace('/[^\w\x{4e00}-\x{9fa5}]/u', '_', $tagName);
                // 静态生成（SSG）走静态目录，动态站走 tag 路由；两者共用本篇模板
                if (defined('STATIC_ROOT_URL')) {
                    $tagUrl = rtrim(STATIC_ROOT_URL, '/') . '/tag/' . $safeTag . '/index.html';
                } else {
                    $tagUrl = '/?tag/' . rawurlencode($tagName) . '/';
                }
                $postCount = count($posts);
                ?>
                <!-- 标签样式：复用现有CSS变量，胶囊式设计 -->
                <a href="<?php echo $tagUrl; ?>" style="
                    display: inline-flex;
                    align-items: center;
                    padding: 8px 16px;
                    background: var(--bg);
                    border: 1px solid #e5e7eb;
                    border-radius: 24px;
                    font-size: 0.95rem;
                    color: var(--text);
                    text-decoration: none;
                    transition: all 0.2s ease;
                ">
                    <span><?php echo htmlspecialchars($tagName); ?></span>
                    <span style="
                        margin-left: 6px;
                        padding: 2px 8px;
                        background: var(--primary);
                        color: white;
                        font-size: 0.8rem;
                        border-radius: 12px;
                    "><?php echo $postCount; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- 标签统计信息 -->
        <div style="
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            color: #6b7280;
        ">
            共 <?php echo count($all_tags); ?> 个标签，包含 <?php echo count($allPosts); ?> 篇文章
        </div>
        
    <?php else: ?>
        <p style="color:#999; text-align:center; padding: 40px 0;">暂无标签数据</p>
    <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>