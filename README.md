# TobPress

一套为个人博客而写的现代 WordPress 主题：混合（Hybrid）架构、设计令牌驱动、零 jQuery、深色模式、长文阅读体验优先。默认配色为清新蓝白（主色 `#2f7fe0`）。

- 版本：1.1.0
- 要求：WordPress 6.6+、PHP 8.0+（开发与实测环境为 WP 7.1 / PHP 8.3）
- 许可证：GPL-2.0-or-later（见 `LICENSE`）
- 更新：`Update URI: https://github.com/tobaye/TobPress`，自托管发布，不会被 WordPress.org 同名主题覆盖更新。

## 架构分工

一条铁律：**同一个设置只有一个家**。要改样式去 theme.json，要改行为去 Customizer，不要双头管理。

| 层 | 负责什么 | 不负责什么 |
|---|---|---|
| `theme.json`（v3） | 颜色（含语义色 base/contrast/surface/border/muted、primary、accent)、字体家族、流式字阶（7 档）、间距预设、内容宽度（800px / 全宽 1200px）、元素与核心块基础样式、编辑器可用项开关 | 业务逻辑、文案、功能开关 |
| PHP 模板 + `template-parts/` | 页面结构、循环、卡片、面包屑、目录、相关文章等服务端渲染 | 硬编码颜色/字号（一律引用令牌变量） |
| Customizer（TobPress 选项） | 仅博客专属开关：主色覆盖、布局（右侧栏/无边栏）、深色默认策略、首页轮播开关、列表样式、相关文章数、ICP 文案/链接、版权文案模板、SEO 总开关 | 颜色盘、字号、间距（归 theme.json) |
| CSS（`style.css`) | 承接 `--wp--preset--*` 令牌 + 自有语义变量（`--tb-bg`、`--tb-surface`、`--tb-text`、`--tb-muted`、`--tb-border` 等）；深色模式只覆盖这组变量 | 组件里写裸十六进制色值 |
| JS（`assets/js/tobpress.js`) | 单文件原生 JS：深色切换、移动菜单、搜索栏、轮播、TOC scrollspy、阅读进度、回到顶部、图片灯箱、评论表情与 Ajax 提交 | 不引 jQuery、不引构建链 |

## 功能清单

### 已实现（v1.0.0)

- [x] theme.json v3 令牌体系 + 编辑器样式 / Style Book 兼容
- [x] 深色模式三态 + 无闪烁初始化（`localStorage` 键 `tobpress-theme`)
- [x] 首页特色轮播（置顶优先，scroll-snap)
- [x] 文章卡片列表两形态（横排列表 / 卡片网格，Customizer 切换）
- [x] 文章目录 TOC + scrollspy、阅读进度条、阅读时长、面包屑
- [x] 版权声明（占位符 `{title}` `{url}` `{author}` `{year}`，`{year}` 为文章发表年份）
- [x] 相关文章（同分类，0–6 篇可调）
- [x] 代码高亮 Prism（仅含代码的单篇页加载，明暗跟随变量）
- [x] 正文图片灯箱（原生 `<dialog>`）
- [x] 评论 Ajax 提交（失败回退原生）+ 表情面板；审核/反垃圾走 WP 核心
- [x] 短代码子集：提示框四色、折叠面板、按钮、下载按钮、登录可见、回复可见
- [x] 内置 SEO:canonical / description / OG / BlogPosting JSON-LD，一键关闭，SEO 插件在场自动让行
- [x] 设置导入导出（工具 → TobPress 设置，theme mods 的 JSON)
- [x] 小工具：关于本站、热门文章、最新评论、站点数据（文章/字数/评论/运行天数）
- [x] 页面模板：友情链接（原生链接管理器）、文章归档（按年月）、说说时间线
- [x] 移动抽屉菜单、顶部搜索栏、回到顶部
- [x] 页脚 ICP 文案与链接、动态版权年份
- [x] 浏览量与文章点赞、评论点赞与博主徽章、分享行、卡片式上下篇、过时提醒、代码复制、打赏弹窗（可选）

### 后续规划（未实现，不要当已有功能）

- 可视化/区块化布局编辑（走 WP 官方区块路线，不自研拖拽器）
- 海报生成
- Banner 式导航头、移动端自定义底栏、瀑布流列表
- 评论置顶、审核策略细化开关
- 前台搜索 REST 实时结果（现已有 Ctrl/⌘+K 唤起的站内搜索面板）
- 文章过时提醒、样式预设多套（夜间/纸感)、访客字体偏好
- 首页独立 SEO 与文章级 SEO 字段

明确不做：Pjax/SPA 无刷新、自研评论系统（保留 WP 原生）、页眉页脚拖拽构建器、论坛、第三方登录、应用市场、主题内置发信。

## 性能预算

- 前台零 jQuery；主题 JS 单文件（约 11KB，未压缩口径），页脚加载。
- `style.css` 约 28KB（未压缩），全部样式集中一份、无按页拆分（v1 取舍：页面类型少，单文件更利于缓存）。
- Prism（JS 约 30KB + CSS 约 2KB）只在正文含 `<code>` 或语言类名的单篇页加载，其余页面零开销。
- 未启用的功能不输出对应资源：关闭轮播不查置顶、相关文章数设 0 不查库、SEO 关了不输出任何标签。
- 推测加载（Speculative Loading）用 WordPress 核心默认，主题不叠加自己的 prefetch/Pjax。

## 安装与升级

安装：后台「外观 → 主题 → 添加 → 上传」选择发布 zip，或把主题目录放进 `wp-content/themes/tobpress/` 后在后台启用。启用后到「自定义 → TobPress 选项」设置主色、布局、ICP 等。

升级：下载新版 zip 后台覆盖上传，或用 git 拉取新 tag 部署到主题目录。设置全部存在 theme mods 中，升级不丢；升级前建议先到「工具 → TobPress 设置」导出一份 JSON 备份。回滚：装回上一个版本的 zip 即可，数据库结构无任何主题私有表。

本地开发自检：改完代码至少跑 `php -l` 逐个检查 PHP 文件、`python3 -m json.tool theme.json` 校验令牌文件、`node --check assets/js/tobpress.js` 校验脚本。

## 子主题

TobPress 的模板全部走模板层级 + `get_template_part()`，可被子主题同名文件覆盖；`template-parts/content-card.php` 与 `content-none.php` 是最常用的覆盖点。改站时建议：

1. 建子主题目录，`style.css` 头写 `Template: tobpress`，`functions.php` 里入队父主题样式（`get_template_directory_uri() . '/style.css'`）。
2. 颜色/字阶微调优先放子主题自己的 `theme.json`（只写要改的键，WordPress 会与父主题合并）。
3. 零散样式放 Customizer 的「额外 CSS」，颜色请引用变量（如 `var(--tb-primary)`、`var(--wp--preset--color--surface)`），这样深色模式会自动跟随。

## 目录结构

```text
tobpress/
├── style.css            主题头（Update URI）+ 全部组件样式
├── theme.json           设计令牌（v3）
├── functions.php        装配：setup、入队、require inc/*
├── header.php / footer.php / sidebar.php / searchform.php
├── index.php / single.php / page.php / archive.php / search.php / 404.php / comments.php
├── template-parts/      content-card.php, content-none.php
├── page-templates/      links.php（友情链接）, archives.php（文章归档）
├── inc/                 template-tags / customizer / widgets / seo / shortcodes / tools
├── assets/js/tobpress.js
├── assets/css/editor-style.css
├── assets/vendor/prism/ Prism 1.29.0 精简打包 + 变量化配色
├── languages/           翻译文件目录（Text Domain: tobpress）
├── README.md / CHANGELOG.md / LICENSE
```

## 发版流程

1. 改 `style.css` 头 `Version:` 与 `functions.php` 的 `TOBPRESS_VERSION`（两者必须一致）。
2. `CHANGELOG.md` 补人话条目。
3. 过发布前检查单：副本环境实测首页/文章/分类/中文编码别名文章/搜索/404/评论；深色三态各截一图；键盘走完菜单与搜索；每个可选功能关闭后各验一次无报错。
4. 打 git tag（如 `v1.0.0`)，打包 zip（只含主题目录本身），GitHub Release 附升级说明与上一版 zip 作为回滚点。

## 致谢与边界

TobPress 为全部原创代码。功能思路参考了 GPL 协议的 CorePress 主题的博客骨架，以及 AeroCore 官网公开的功能清单（商业主题，仅借鉴功能点，未使用其任何代码）。Prism 为 MIT 许可的第三方库，署名见其打包文件头。
