/**
 * Genspark 自動化スクリプト
 *
 * Playwright を使って Genspark にプロンプトを自動入力し、
 * 生成された Sparkpage の内容を保存する。
 *
 * 使い方:
 *   node genspark-automation.mjs                    # 対話モード（プロンプト選択）
 *   node genspark-automation.mjs --type all         # 全プロンプトを順番に実行
 *   node genspark-automation.mjs --type slides      # スライド用プロンプトのみ
 *   node genspark-automation.mjs --type docs        # ドキュメント用プロンプトのみ
 *   node genspark-automation.mjs --prompt "質問"    # カスタムプロンプトを直接入力
 *   node genspark-automation.mjs --headed           # ブラウザを表示して実行（デフォルト）
 *   node genspark-automation.mjs --headless         # ヘッドレスモードで実行
 */

import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// ── 設定 ──────────────────────────────────────────
const CONFIG = {
  gensparkUrl: 'https://www.genspark.ai/',
  userDataDir: path.join(__dirname, '.browser-data'),
  outputDir: path.join(__dirname, '..', 'docs', 'output', 'genspark-results'),
  promptsFile: path.join(__dirname, '..', 'docs', 'output', 'genspark-prompts.md'),
  timeout: 120_000,       // Sparkpage生成の最大待機時間 (2分)
  inputDelay: 50,         // タイピング速度 (ms/char)
  pageLoadWait: 5_000,    // ページ読み込み後の追加待機 (ms)
  betweenPrompts: 3_000,  // プロンプト間の待機時間 (ms)
};

// ── プロンプト解析 ────────────────────────────────
function parsePromptsFromMarkdown(filePath) {
  if (!fs.existsSync(filePath)) {
    console.error(`プロンプトファイルが見つかりません: ${filePath}`);
    process.exit(1);
  }

  const content = fs.readFileSync(filePath, 'utf-8');
  const prompts = [];
  const codeBlockRegex = /###\s+(.+?)\n\n```\n([\s\S]*?)```/g;

  let match;
  while ((match = codeBlockRegex.exec(content)) !== null) {
    const title = match[1].trim();
    const prompt = match[2].trim();
    prompts.push({ title, prompt });
  }

  return prompts;
}

// ── カテゴリフィルタ ──────────────────────────────
function filterPromptsByType(prompts, type) {
  if (!type || type === 'all') return prompts;

  const filters = {
    slides: ['スライド', 'プレゼン', '概要'],
    docs: ['ドキュメント', '仕様書', '設計', 'チェックリスト'],
    search: ['検索', '最新情報', 'ベストプラクティス'],
    security: ['セキュリティ', 'OWASP'],
    architecture: ['構成図', 'アーキテクチャ', '設計パターン'],
  };

  const keywords = filters[type];
  if (!keywords) {
    console.error(`不明なタイプ: ${type}。使用可能: ${Object.keys(filters).join(', ')}`);
    process.exit(1);
  }

  return prompts.filter(p =>
    keywords.some(kw => p.title.includes(kw) || p.prompt.includes(kw))
  );
}

// ── 出力保存 ──────────────────────────────────────
function saveResult(title, content, index) {
  if (!fs.existsSync(CONFIG.outputDir)) {
    fs.mkdirSync(CONFIG.outputDir, { recursive: true });
  }

  const safeTitle = title
    .replace(/[<>:"/\\|?*]/g, '_')
    .replace(/\s+/g, '_')
    .substring(0, 60);

  const filename = `${String(index + 1).padStart(2, '0')}_${safeTitle}.md`;
  const filePath = path.join(CONFIG.outputDir, filename);

  fs.writeFileSync(filePath, `# ${title}\n\n${content}\n`, 'utf-8');
  console.log(`  保存: ${filename}`);
  return filePath;
}

// ── Genspark自動操作 ──────────────────────────────
async function inputPromptToGenspark(page, promptText) {
  // Genspark のトップページに移動
  await page.goto(CONFIG.gensparkUrl, { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(CONFIG.pageLoadWait);

  // 入力欄を探す（複数のセレクタパターンで試行）
  const inputSelectors = [
    'textarea[placeholder]',
    'input[type="text"][placeholder]',
    'textarea',
    '[contenteditable="true"]',
    'input[type="search"]',
    '.search-input',
    '#search-input',
    '[role="textbox"]',
    '[data-testid="search-input"]',
  ];

  let inputElement = null;
  for (const selector of inputSelectors) {
    try {
      inputElement = await page.waitForSelector(selector, { timeout: 5_000 });
      if (inputElement) {
        console.log(`  入力欄を検出: ${selector}`);
        break;
      }
    } catch {
      // 次のセレクタを試す
    }
  }

  if (!inputElement) {
    console.error('  入力欄が見つかりません。手動で入力欄をクリックしてください。');
    // フォールバック: ユーザーが手動でクリックした要素を使う
    console.log('  30秒以内に入力欄をクリックしてください...');
    await page.waitForTimeout(30_000);
    return null;
  }

  // 入力欄をクリックしてフォーカス
  await inputElement.click();
  await page.waitForTimeout(500);

  // 既存のテキストをクリア
  await page.keyboard.press('Control+a');
  await page.keyboard.press('Backspace');

  // プロンプトを入力（クリップボード経由で高速入力）
  await page.evaluate((text) => {
    navigator.clipboard.writeText(text);
  }, promptText).catch(() => {
    // clipboard API が使えない場合はフォールバック
  });

  // クリップボード経由の貼り付けを試す
  try {
    await page.keyboard.press('Control+v');
    await page.waitForTimeout(500);

    // 貼り付けが成功したか確認
    const inputValue = await inputElement.inputValue().catch(() => null)
      || await inputElement.textContent().catch(() => null)
      || '';

    if (inputValue.length < 10) {
      // 貼り付け失敗時は直接入力
      await inputElement.fill(promptText);
    }
  } catch {
    // fill でフォールバック
    await inputElement.fill(promptText);
  }

  await page.waitForTimeout(500);

  // 送信ボタンを探してクリック
  const submitSelectors = [
    'button[type="submit"]',
    'button:has(svg)',
    '[aria-label="Search"]',
    '[aria-label="送信"]',
    '[aria-label="Submit"]',
    'button.search-button',
    'button:near(textarea)',
  ];

  let submitted = false;
  for (const selector of submitSelectors) {
    try {
      const btn = await page.waitForSelector(selector, { timeout: 3_000 });
      if (btn) {
        await btn.click();
        submitted = true;
        console.log(`  送信ボタンを検出: ${selector}`);
        break;
      }
    } catch {
      // 次のセレクタを試す
    }
  }

  if (!submitted) {
    // Enter キーで送信を試みる
    await page.keyboard.press('Enter');
    console.log('  Enterキーで送信');
  }

  return true;
}

async function waitForSparkpage(page) {
  console.log('  Sparkpage の生成を待機中...');

  // URLが変わるまで待つ（/spark/ パスに遷移するはず）
  try {
    await page.waitForURL(/\/(spark|search|chat)\//, {
      timeout: CONFIG.timeout,
    });
  } catch {
    console.log('  URL変更を検出できませんでした。現在のページから結果を取得します。');
  }

  // コンテンツの生成完了を待つ
  // ローディング表示が消えるまで待機
  const loadingSelectors = [
    '.loading',
    '[class*="loading"]',
    '[class*="spinner"]',
    '[class*="generating"]',
    '.animate-pulse',
  ];

  for (const selector of loadingSelectors) {
    try {
      await page.waitForSelector(selector, { state: 'hidden', timeout: CONFIG.timeout });
      break;
    } catch {
      // 次のセレクタを試す
    }
  }

  // 追加の安定待機
  await page.waitForTimeout(5_000);
}

async function extractSparkpageContent(page) {
  // ページのテキストコンテンツを取得
  const content = await page.evaluate(() => {
    // メインコンテンツエリアを探す
    const contentSelectors = [
      'article',
      '[class*="sparkpage"]',
      '[class*="content"]',
      '[class*="result"]',
      '[class*="answer"]',
      'main',
      '.markdown-body',
      '[role="main"]',
    ];

    for (const selector of contentSelectors) {
      const el = document.querySelector(selector);
      if (el && el.textContent.trim().length > 100) {
        return el.textContent.trim();
      }
    }

    // フォールバック: body全体から nav/header/footer を除外
    const body = document.body.cloneNode(true);
    ['nav', 'header', 'footer', 'aside', 'script', 'style'].forEach(tag => {
      body.querySelectorAll(tag).forEach(el => el.remove());
    });
    return body.textContent.trim();
  });

  return content;
}

// ── メイン処理 ────────────────────────────────────
async function main() {
  const args = process.argv.slice(2);
  const isHeadless = args.includes('--headless');
  const typeIndex = args.indexOf('--type');
  const promptIndex = args.indexOf('--prompt');
  const type = typeIndex >= 0 ? args[typeIndex + 1] : null;
  const customPrompt = promptIndex >= 0 ? args[promptIndex + 1] : null;

  // プロンプト準備
  let prompts;
  if (customPrompt) {
    prompts = [{ title: 'カスタムプロンプト', prompt: customPrompt }];
  } else {
    const allPrompts = parsePromptsFromMarkdown(CONFIG.promptsFile);
    prompts = filterPromptsByType(allPrompts, type);
  }

  if (prompts.length === 0) {
    console.error('実行するプロンプトがありません。');
    process.exit(1);
  }

  console.log(`\n=== Genspark 自動化 ===`);
  console.log(`プロンプト数: ${prompts.length}`);
  console.log(`モード: ${isHeadless ? 'ヘッドレス' : 'ブラウザ表示'}`);
  console.log(`出力先: ${CONFIG.outputDir}\n`);

  // ブラウザ起動（永続コンテキスト: ログイン状態を保持）
  const context = await chromium.launchPersistentContext(CONFIG.userDataDir, {
    headless: isHeadless,
    viewport: { width: 1280, height: 900 },
    locale: 'ja-JP',
    args: [
      '--disable-blink-features=AutomationControlled',
    ],
  });

  const page = context.pages()[0] || await context.newPage();

  // 初回アクセスでログイン確認
  await page.goto(CONFIG.gensparkUrl, { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(CONFIG.pageLoadWait);

  // ログインが必要か確認
  const isLoggedIn = await page.evaluate(() => {
    const loginButton = document.querySelector('[href*="login"], [href*="signin"], button:has-text("Log in"), button:has-text("Sign in")');
    return !loginButton;
  }).catch(() => true);

  if (!isLoggedIn) {
    console.log('ログインが必要です。ブラウザでログインしてください。');
    console.log('ログイン完了後、自動的に続行します。（最大5分待機）\n');

    // ログイン完了を待つ
    try {
      await page.waitForURL(url => !url.toString().includes('login') && !url.toString().includes('signin'), {
        timeout: 300_000, // 5分
      });
      console.log('ログイン確認。処理を続行します。\n');
    } catch {
      console.error('ログインタイムアウト。スクリプトを終了します。');
      await context.close();
      process.exit(1);
    }
  }

  // 各プロンプトを実行
  const results = [];
  for (let i = 0; i < prompts.length; i++) {
    const { title, prompt } = prompts[i];
    console.log(`\n[${i + 1}/${prompts.length}] ${title}`);
    console.log(`  プロンプト: ${prompt.substring(0, 80)}...`);

    try {
      const success = await inputPromptToGenspark(page, prompt);
      if (success === null) {
        console.log('  スキップ');
        continue;
      }

      await waitForSparkpage(page);
      const content = await extractSparkpageContent(page);

      const savedPath = saveResult(title, content, i);
      results.push({ title, savedPath, success: true });

      console.log(`  完了 (${content.length} 文字)`);
    } catch (error) {
      console.error(`  エラー: ${error.message}`);
      results.push({ title, success: false, error: error.message });
    }

    // 次のプロンプトの前に待機
    if (i < prompts.length - 1) {
      await page.waitForTimeout(CONFIG.betweenPrompts);
    }
  }

  // サマリー出力
  console.log('\n\n=== 実行結果サマリー ===');
  console.log(`成功: ${results.filter(r => r.success).length}/${results.length}`);
  results.forEach(r => {
    const status = r.success ? 'OK' : 'NG';
    console.log(`  [${status}] ${r.title}`);
  });
  console.log(`\n出力先: ${CONFIG.outputDir}`);

  await context.close();
}

main().catch(error => {
  console.error('致命的エラー:', error);
  process.exit(1);
});
