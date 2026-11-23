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
 * @File         admin.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Timed popup panel: enable checkbox, repeat interval and a shortcut to the countdown-related tools.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?> <div class="clear"></div>
 <div class="field">
  <div class="label">
<?= L('yanhua'); ?>
  </div>
<div class="label">
<input type="checkbox" name="yanhua" <?= isset($yanhua) && $yanhua ? 'checked' : '' ?>>

</div>
<div class="info">
	是否开启定时：
	<input type="checkbox" name="showPopup" <?= isset($showPopup) && $showPopup ? 'checked' : '' ?>>
	定时为每
	<input type="number" style="width:100px" name="timesec" value="<?= isset($timesec) ? $timesec : ''; ?>">
秒显示一次  <a href="javascript:void(0);" class="button" onclick="openModal()">打开计算器</a>
</div>
</div>

    <style>
        /* 模态框的样式 */
        .modal {
            display: none; /* 默认不显示 */
            position: fixed;
            z-index: 1; /* 确保模态框位于其他内容上方 */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); /* 背景色，半透明 */
            padding-top: 60px;
            transition: all 0.3s ease;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 50%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* 添加阴影效果 */
            text-align: center;
        }

        .modal-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            color: #333;
            cursor: pointer;
            font-weight: bold;
        }

        .modal-close:hover {
            color: #ff0000;
        }

        /* 让链接样式看起来像按钮 */
        .open-modal-btn {
    display: inline-block;
    padding: 5px 10px;
    font-size: 18px;
    /* background-color: #e6db55; */
    /* color: white; */
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
        }

        .open-modal-btn:hover {
            background-color: #45a049;
        }

        /* 表单部分 */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 16px;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="number"] {
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ddd;
            outline: none;
            transition: all 0.3s ease;
        }

        input[type="number"]:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }

        .result {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            background-color: #f0f8ff;
            padding: 10px;
            border-radius: 8px;
        }

        .result span {
            color: #4CAF50;
        }
    </style>
<!-- 模态框 -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h2>时间转秒计算器</h2>
            <!-- 计算器内容 -->
            <form id="time-form">
                <div>
                    <label for="hours">小时:</label>
                    <input type="number" id="hours" name="hours" value="0" min="0" placeholder="0">
                </div>
                <div>
                    <label for="minutes">分钟:</label>
                    <input type="number" id="minutes" name="minutes" value="0" min="0" max="59" placeholder="0">
                </div>
                <div>
                    <label for="seconds">秒:</label>
                    <input type="number" id="seconds" name="seconds" value="0" min="0" max="59" placeholder="0">
                </div>
            </form>

            <div class="result">
                <span id="total-seconds">总秒数: 0</span>
            </div>
        </div>
    </div>

    <script>
        // 获取输入字段
        const hoursInput = document.getElementById('hours');
        const minutesInput = document.getElementById('minutes');
        const secondsInput = document.getElementById('seconds');
        const totalSecondsDisplay = document.getElementById('total-seconds');
        
        // 计算秒数并更新显示
        function updateSeconds() {
            const hours = parseInt(hoursInput.value) || 0;
            const minutes = parseInt(minutesInput.value) || 0;
            const seconds = parseInt(secondsInput.value) || 0;

            const totalSeconds = (hours * 3600) + (minutes * 60) + seconds;
            totalSecondsDisplay.textContent = `总秒数: ${totalSeconds}`;
        }

        // 绑定输入事件
        hoursInput.addEventListener('input', updateSeconds);
        minutesInput.addEventListener('input', updateSeconds);
        secondsInput.addEventListener('input', updateSeconds);

        // 初始化显示
        updateSeconds();

        // 打开模态框
        function openModal() {
            document.getElementById("myModal").style.display = "block";
        }

        // 关闭模态框
        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        // 点击模态框外部区域时关闭模态框
        window.onclick = function(event) {
            const modal = document.getElementById("myModal");
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>