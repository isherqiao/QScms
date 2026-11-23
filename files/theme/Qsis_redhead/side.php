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
 * @File         side.php
 * @CreatedTime  05/11/26 21:16:59
 * @LastEditTime 05/11/26 21:16:59
 * @Description  Template of the theme "Qsis_redhead": sidebar block.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>    <div id="side">

      <div class="amenu">
          <div class="amenu_a">

</div>
          <div class="amenu_a">
<a href="<?php qs_site_link(); ?>/?archive/" class="archive" title="文章存档">存档</a>
</div>
          <div class="amenu_a">
<a href="<?php qs_site_link(); ?>/?contact/" class="contact" title="联系方式">联系</a>
</div>
          <div class="amenu_a">
              <!--
<a href="<?php qs_site_link(); ?>/?rss/" class="rss" title="RSS订阅" target="_blank">RSS</a>
-->
</div>
          <div class="amenu_a">
<a href="<?php qs_site_link(); ?>/liuyan.php" class="rss" title="留言" target="_blank">留言</a>
</div>
          <div class="amenu_a">
<a href="<?php qs_site_link(); ?>/?service/"  class="contact" class="amenu_m">服务</a>
</div>

</div>
      <div class="amenu">
        <input type="text" name="search" class="search text" id="text" value="" /> <input type="button" class="submit" value="标题搜索" onclick="Search()" />
</div>
</div>