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
 * @Description  IPv4/IPv6 redirect panel: enables the automatic redirect and collects the IPv4 and IPv6 site addresses.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

?>
<div class="clear"></div>
                <div class="field">
                    <div class="label">
<?= L('0EAT8'); ?>
<script language="javascript">
function auto46change(auto46)
{
//alert(auto46.value);
if(auto46.value=="1")
{
document.getElementById("v4v6").style.display = 'block';
}else if(auto46.value=="0"){
document.getElementById("v4v6").style.display = 'none';
}
}
</script>

                    </div>
                    <div class="label">
<select class="select-1" name="v4v6auto" id="auto46" onchange="auto46change(this);">
<option value="1" <?php if ($v4v6auto == "1") { echo "selected";
}
?>><?= L('Enable'); ?></option>
<option value="0" <?php if ($v4v6auto == "0") { echo "selected";
}
?>><?= L('Disable'); ?></option>
</select>
 </div>
                    <div class="info">
<?= L('0EA8'); ?>
                    </div>
                </div>

<div id="v4v6" <?php if($v4v6auto == "1") { echo 'style="display:block"'; } elseif ($v4v6auto == "0") { echo 'style="display:none"'; }
                                ?>>
<div class="clear"></div>
                <div class="field">
                    <div class="label">
                        网站地址(IPv4)
                    </div>
                    <div class="labec">
                    <input class="input" placeholder="http://" type="text" name="site_link_ipv4" value="<?php echo htmlspecialchars($site_link_ipv4);
                    ?>" />
                     </div>
                    <div class="info">
                        可留空,虚拟目录后面加“/”。A与AAAA解析相同可留空
                    </div>
                </div>
                
<div class="clear"></div>
                <div class="field">
                    <div class="label">
                        网站地址(IPv6)
                    </div>
                    <div class="labec">
                    <input class="input" placeholder="http://" type="text" name="site_link_ipv6" value="<?php echo htmlspecialchars($site_link_ipv6);
                    ?>" />
                    </div>
                    <div class="info">
                        可留空,虚拟目录后面加“/”。A与AAAA解析相同可留空
                    </div>
                </div>
          </div>      
                