<?php
/*
 * QScms (Qsiscms) - Multi-language Lightweight Content Management System
 *
 * Copyright (C) 2016-2026 Qiao Shuo (isherqiao) <qiaoshuo@gmail.com>
 *
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * This file is part of QScms.
 */

/**
 * @File         index.php
 * @Description  Template of the theme "Qsis_business": industrial corporate home page.
 * @copyright    2016-2026 Qiaoshuo 乔硕 (isherqiao)
 */

if (!defined('SITE_ROOT')) exit;

include __DIR__ . '/theme_config.php';

$qs_biz_site = isset($qs_config['site_name']) ? (string)$qs_config['site_name'] : 'QScms';
$qs_biz_desc = isset($qs_config['site_desc']) ? (string)$qs_config['site_desc'] : '';
$qs_biz_base = rtrim((string)qs_site_link(false), '/');

$qs_biz_o = is_array($theme_options) ? $theme_options : array();

$qs_biz_link = function ($link, $base) {
    $link = trim((string)$link);
    if ($link === '') {
        return $base . '/';
    }
    if (preg_match('#^(https?:)?//#i', $link) || $link[0] === '#' || $link[0] === '/') {
        return $link;
    }
    return $base . '/' . ltrim($link, '/');
};

$qs_biz_title = !empty($qs_biz_o['banner_title']) ? $qs_biz_o['banner_title'] : '规范流程，稳定交付';
$qs_biz_sub   = !empty($qs_biz_o['banner_sub'])
    ? $qs_biz_o['banner_sub']
    : ($qs_biz_desc !== '' ? mb_substr($qs_biz_desc, 0, 96) : '规范化流程、可追溯记录、稳定的交付节奏，是我们对每一个项目的承诺。');

$qs_biz_facts = qs_theme_opts_rows(isset($qs_biz_o['facts']) ? $qs_biz_o['facts'] : '', 1);
$qs_biz_services = qs_theme_opts_rows(isset($qs_biz_o['services']) ? $qs_biz_o['services'] : '', 3);
$qs_biz_adv = qs_theme_opts_rows(isset($qs_biz_o['adv_items']) ? $qs_biz_o['adv_items'] : '', 1);
if (count($qs_biz_facts) > 6) {
    $qs_biz_facts = array_slice($qs_biz_facts, 0, 6);
}
if (count($qs_biz_services) > 6) {
    $qs_biz_services = array_slice($qs_biz_services, 0, 6);
}
if (count($qs_biz_adv) > 6) {
    $qs_biz_adv = array_slice($qs_biz_adv, 0, 6);
}
$qs_biz_feed = isset($qs_biz_o['feed_count']) ? (int)$qs_biz_o['feed_count'] : 8;
if ($qs_biz_feed < 1) {
    $qs_biz_feed = 8;
}
$qs_biz_contact_link = $qs_biz_link(isset($qs_biz_o['banner_btn1_link']) ? $qs_biz_o['banner_btn1_link'] : '/contact.html', $qs_biz_base);
$qs_biz_banner_bg = isset($qs_biz_o['banner_bg']) ? trim((string)$qs_biz_o['banner_bg']) : '';

include 'header.php';

if (qs_is_post() || qs_is_page()) {
    include 'post.php';
} elseif (qs_is_tags()) {
    include 'tags.php';
} elseif (qs_is_tag()) {
    include 'list.php';
} else {
?>

<section class="banner<?php echo $qs_biz_banner_bg !== '' ? ' has-bg' : ''; ?>">
    <?php if ($qs_biz_banner_bg !== ''): ?>
    <div class="banner-bg" aria-hidden="true"><img src="<?php echo htmlspecialchars(qs_theme_url($qs_biz_banner_bg, false)); ?>" alt=""></div>
    <?php endif; ?>
    <div class="wrap banner-inner">
        <div class="banner-copy">
            <p class="banner-kicker"><?php echo htmlspecialchars($qs_biz_site); ?></p>
            <h1><?php echo htmlspecialchars($qs_biz_title); ?></h1>
            <p class="banner-sub"><?php echo htmlspecialchars($qs_biz_sub); ?></p>
            <div class="banner-actions">
                <a class="biz-btn biz-btn-solid" href="<?php echo htmlspecialchars($qs_biz_contact_link); ?>"><?php echo htmlspecialchars(!empty($qs_biz_o['banner_btn1_text']) ? $qs_biz_o['banner_btn1_text'] : '获取方案'); ?></a>
                <a class="biz-btn biz-btn-line" href="<?php echo htmlspecialchars($qs_biz_link(isset($qs_biz_o['banner_btn2_link']) ? $qs_biz_o['banner_btn2_link'] : '#services', $qs_biz_base)); ?>"><?php echo htmlspecialchars(!empty($qs_biz_o['banner_btn2_text']) ? $qs_biz_o['banner_btn2_text'] : '查看服务'); ?></a>
            </div>
        </div>
        <?php if ($qs_biz_facts): ?>
        <div class="banner-facts">
            <p class="fact-label">服务概览</p>
            <ul class="fact-list">
                <?php foreach ($qs_biz_facts as $qs_biz_one): ?>
                <li><?php echo htmlspecialchars($qs_biz_one[0]); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($qs_biz_services): ?>
<section class="biz-section" id="services">
    <div class="wrap">
        <header class="biz-head">
            <p class="biz-head-label">Services</p>
            <h2><?php echo htmlspecialchars(!empty($qs_biz_o['services_title']) ? $qs_biz_o['services_title'] : '服务矩阵'); ?></h2>
            <p class="biz-head-note"><?php echo htmlspecialchars(!empty($qs_biz_o['services_note']) ? $qs_biz_o['services_note'] : '按项目生命周期划分，每一段都有明确的输入、输出与验收标准。'); ?></p>
        </header>
        <div class="biz-grid">
            <?php $qs_biz_svc_i = 0; foreach ($qs_biz_services as $qs_biz_one): $qs_biz_svc_i++;
                  $qs_biz_svc_bg = isset($qs_biz_o['svc' . $qs_biz_svc_i . '_bg']) ? trim((string)$qs_biz_o['svc' . $qs_biz_svc_i . '_bg']) : ''; ?>
            <article class="biz-cell<?php echo $qs_biz_svc_bg !== '' ? ' has-bg' : ''; ?>">
                <?php if ($qs_biz_svc_bg !== ''): ?>
                <div class="biz-cell-bg" aria-hidden="true"><img src="<?php echo htmlspecialchars(qs_theme_url($qs_biz_svc_bg, false)); ?>" alt=""></div>
                <?php endif; ?>
                <?php if ($qs_biz_one[0] !== ''): ?>
                <span class="biz-cell-no"><?php echo htmlspecialchars($qs_biz_one[0]); ?></span>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($qs_biz_one[1]); ?></h3>
                <p><?php echo htmlspecialchars($qs_biz_one[2]); ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($qs_biz_adv && (!isset($qs_biz_o['adv_show']) || $qs_biz_o['adv_show'] === '1')): ?>
<section class="biz-advantages">
    <div class="wrap biz-adv-inner">
        <div class="biz-adv-copy">
            <header class="biz-head">
                <p class="biz-head-label">Why us</p>
                <h2><?php echo htmlspecialchars(!empty($qs_biz_o['adv_title']) ? $qs_biz_o['adv_title'] : '把不确定留给沟通，把确定交给流程'); ?></h2>
            </header>
            <ul class="biz-adv-list">
                <?php foreach ($qs_biz_adv as $qs_biz_one): ?>
                <li><?php echo htmlspecialchars($qs_biz_one[0]); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="biz-adv-figure">
            <p class="adv-num"><?php echo (int)$qs_post_count; ?>+</p>
            <p class="adv-cap"><?php echo htmlspecialchars(!empty($qs_biz_o['adv_caption']) ? $qs_biz_o['adv_caption'] : '已公开的案例与说明'); ?></p>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="biz-section biz-section-alt">
    <div class="wrap">
        <header class="biz-head">
            <p class="biz-head-label">Insights</p>
            <h2><?php echo htmlspecialchars(!empty($qs_biz_o['feed_title']) ? $qs_biz_o['feed_title'] : '案例与动态'); ?></h2>
        </header>
        <div class="biz-list">
            <?php if (qs_next_post()): ?>
            <?php $qs_biz_i = 0; do { $qs_biz_i++; if ($qs_biz_i > $qs_biz_feed) break; ?>
            <article class="biz-list-item">
                <p class="biz-list-date"><?= qs_the_date() ?></p>
                <h3 class="biz-list-title"><a href="<?php qs_post_link(); ?>"><?= qs_the_title() ?></a></h3>
            </article>
            <?php } while (qs_next_post()); ?>
            <?php else: ?>
            <p class="biz-list-empty">暂无内容。</p>
            <?php endif; ?>
        </div>
        <?php qs_pagination(); ?>
    </div>
</section>

<?php if (!isset($qs_biz_o['contact_show']) || $qs_biz_o['contact_show'] === '1'): ?>
<section class="biz-contact-band">
    <div class="wrap biz-contact-inner">
        <div>
            <h2><?php echo htmlspecialchars(!empty($qs_biz_o['contact_title']) ? $qs_biz_o['contact_title'] : '需要一份可执行的方案'); ?></h2>
            <p><?php echo htmlspecialchars(!empty($qs_biz_o['contact_sub']) ? $qs_biz_o['contact_sub'] : '告诉我们项目背景与目标，我们会给出初步判断与下一步建议。'); ?></p>
        </div>
        <a class="biz-btn biz-btn-solid" href="<?php echo htmlspecialchars($qs_biz_contact_link); ?>"><?php echo htmlspecialchars(!empty($qs_biz_o['contact_btn']) ? $qs_biz_o['contact_btn'] : '联系我们'); ?></a>
    </div>
</section>
<?php endif; ?>

<?php
}

include 'footer.php';
