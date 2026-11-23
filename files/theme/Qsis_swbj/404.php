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
 * @File         404.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_swbj": not-found page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

 include 'header.php'; ?>
    <style>

        /* 顶部导航栏（和现有页面一致） */

        /* 主体内容区 */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .containerb {
            background: #ffffff;
            border-radius: 4px;
            padding: 60px 40px;
            text-align: center;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #6B8088;
            line-height: 1;
            margin-bottom: 25px;
        }
        .error-title {
            font-size: 32px;
            color: #333333;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .error-desc {
            font-size: 18px;
            color: #555555;
            line-height: 1.8;
            margin-bottom: 40px;
        }
        .btn-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 35px;
            border-radius: 4px;
            font-size: 18px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        /* 主按钮：暖棕填充（匹配现有页面主色） */
        .btn-primary {
            background: #6B8088;
            color: #ffffff;
            border: none;
        }
        .btn-primary:hover {
            background: #92aeb9;
        }
        /* 次按钮：暖棕边框 */
        .btn-outline {
            background: transparent;
            color: #6B8088;
            border: 2px solid #6B8088;
        }
        .btn-outline:hover {
            background: rgba(153, 85, 34, 0.05);
        }

    </style>
    <!-- 404主体内容 -->
    <main>
        <div class="containerb">
            <div class="error-code">404</div>
            <div class="error-title">页面不存在</div>
            <div class="error-desc">
                你访问的页面可能已被删除、链接失效，或输入了错误的地址。<br>
                回到首页，继续探索深度思考与认知成长的内容吧。
            </div>
            <div class="btn-group">
                <a href="/" class="btn btn-primary">返回首页</a>
                <a href="/tag/人文笔记/" class="btn btn-outline">浏览文章</a>
            </div>
        </div>
    </main>
<?php include 'footer.php'; ?>