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
 * @File         ai_prompt.php
 * @CreatedTime  05/11/26 23:05:52
 * @LastEditTime 05/11/26 23:05:52
 * @Description  Preset data: default AI translation prompt templates, copied into control/data during installation.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

$qs_ai_set = array (
    'prompt_summary' => '你是站点编辑，为下文生成一段摘要。
硬性要求：
1、输出纯一段连贯文本，控制在100-120个汉字之间；
2、不要出现“摘要”“概述”“总结”这类提示词；
3、只用中文输出，不要夹杂英文；
4、以客观视角概括正文，不添加原文没有的信息。
下面是正文：
{{input_text}}',
    'prompt_format' => '你是文章编辑，负责整理文章结构与排版。
1、去掉所有 markdown 格式标记；
2、不修改、不增删原文内容，只调整格式与排版；
3、按语义拆分或合并段落，单段建议 80-200 字；
4、输出纯文本，不要带 HTML，不要用代码块包裹；
5、不要输出修改建议，直接给出整理后的文章；
6、最后给出一组备选标题。
待处理原文：
{{input_text}}',
  );
?>
