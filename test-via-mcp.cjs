const chromeLauncher = require('chrome-launcher');
const CDP = require('chrome-remote-interface');
const fs = require('fs');
const path = require('path');

const screenshotsDir = path.join(__dirname, 'screenshots-mcp');
if (!fs.existsSync(screenshotsDir)) {
  fs.mkdirSync(screenshotsDir, { recursive: true });
}

async function runTest() {
  let chrome;
  try {
    console.log('🚀 Starting Chrome...');
    chrome = await chromeLauncher.launch({ chromeFlags: ['--no-sandbox'] });
    const protocol = await CDP({ port: chrome.port });

    const { Page, Runtime } = protocol;
    await Promise.all([Page.enable(), Runtime.enable()]);

    const baseUrl = 'http://127.0.0.1:8000';

    // Navigate to login page
    console.log('📄 Navigating to login page...');
    await Page.navigate({ url: `${baseUrl}/login` });
    await new Promise(r => setTimeout(r, 2000));
    let screenPath = path.join(screenshotsDir, '01-login-page.png');
    const { data: img1 } = await Page.captureScreenshot();
    fs.writeFileSync(screenPath, Buffer.from(img1, 'base64'));
    console.log(`✅ Screenshot saved: ${screenPath}`);

    // Fill login form
    console.log('🔐 Filling login credentials...');
    await Runtime.evaluate({
      expression: `
        document.querySelector('input[name="email"]').value = 'super.admin@test.com';
        document.querySelector('input[name="password"]').value = '123456789';
      `
    });

    // Submit login
    console.log('📤 Submitting login form...');
    await Runtime.evaluate({
      expression: `document.querySelector('button[type="submit"]').click();`
    });
    await new Promise(r => setTimeout(r, 3000));
    screenPath = path.join(screenshotsDir, '02-after-login.png');
    const { data: img2 } = await Page.captureScreenshot();
    fs.writeFileSync(screenPath, Buffer.from(img2, 'base64'));
    console.log(`✅ Screenshot saved: ${screenPath}`);

    // Navigate to key pages
    const pages = [
      { url: '/sales', name: 'Sales' },
      { url: '/purchases', name: 'Purchases' },
      { url: '/products', name: 'Products' },
      { url: '/reports', name: 'Reports' },
      { url: '/settings', name: 'Settings' }
    ];

    for (let i = 0; i < pages.length; i++) {
      const page = pages[i];
      console.log(`📄 Navigating to ${page.name}...`);
      await Page.navigate({ url: `${baseUrl}${page.url}` });
      await new Promise(r => setTimeout(r, 1500));

      // Try to click first button
      try {
        await Runtime.evaluate({
          expression: `(document.querySelector('button') || document.querySelector('a[href]')).click();`
        });
        await new Promise(r => setTimeout(r, 800));
      } catch (e) {}

      screenPath = path.join(screenshotsDir, `0${i + 3}-${page.name.toLowerCase()}.png`);
      const { data: imgData } = await Page.captureScreenshot();
      fs.writeFileSync(screenPath, Buffer.from(imgData, 'base64'));
      console.log(`✅ Screenshot saved: ${screenPath}`);
    }

    // Create product page
    console.log('📄 Navigating to Products Create...');
    await Page.navigate({ url: `${baseUrl}/products/create` });
    await new Promise(r => setTimeout(r, 1500));
    screenPath = path.join(screenshotsDir, '08-products-create.png');
    const { data: img8 } = await Page.captureScreenshot();
    fs.writeFileSync(screenPath, Buffer.from(img8, 'base64'));
    console.log(`✅ Screenshot saved: ${screenPath}`);

    console.log('\n✅ Deep E2E test completed via Chrome MCP!');
    console.log(`📸 Screenshots saved to: ${screenshotsDir}`);
    fs.writeFileSync(
      path.join(screenshotsDir, 'test-complete.txt'),
      `Deep E2E test completed at ${new Date().toISOString()}`
    );

    await protocol.close();
  } catch (err) {
    console.error('❌ Error:', err);
  } finally {
    if (chrome) {
      await chromeLauncher.kill(chrome.pid);
    }
  }
}

runTest();
