"""Offline browser checks for portal layout, navigation and membership payments.
Run php scripts/render-ui-audit.php first. No requests reach the real API.
"""
import json
from pathlib import Path
from urllib.parse import urlparse
from playwright.sync_api import sync_playwright, expect

ROOT = Path(__file__).resolve().parents[1]
FIXTURES = ROOT / 'storage/app/ui-audit'
CONTRAST = r'''() => {
    const rgb = s => (s.match(/[\d.]+/g) || []).map(Number);
    const blend = (a,b) => a.slice(0,3).map((v,i) => v * (a[3] ?? 1) + b[i] * (1-(a[3] ?? 1)));
    const lum = a => a.map(v => v/255).map(v => v <= .04045 ? v/12.92 : ((v+.055)/1.055)**2.4).reduce((s,v,i) => s+v*[.2126,.7152,.0722][i],0);
    const issues = [];
    for (const el of document.querySelectorAll('body *')) {
        if (!el.checkVisibility({checkOpacity:true,checkVisibilityCSS:true}) || el.closest('[inert],button:disabled,[hidden]')) continue;
        const text = [...el.childNodes].filter(n=>n.nodeType===3).map(n=>n.textContent.trim()).join(' ').trim();
        if (!text || ['SCRIPT','STYLE','OPTION'].includes(el.tagName)) continue;
        let nodes=[], gradient=false, opacity=1;
        for(let p=el; p; p=p.parentElement){ const s=getComputedStyle(p); nodes.push(rgb(s.backgroundColor)); opacity*=Number(s.opacity); if(s.backgroundImage!=='none')gradient=true; }
        if(gradient)continue;
        let bg=[255,255,255]; for(const color of nodes.reverse())bg=blend(color,bg);
        const s=getComputedStyle(el), fg=rgb(s.color); fg[3]=(fg[3]??1)*opacity;
        const a=lum(blend(fg,bg)), b=lum(bg), ratio=(Math.max(a,b)+.05)/(Math.min(a,b)+.05);
        const limit=parseFloat(s.fontSize)>=24 || (parseFloat(s.fontSize)>=18.66 && Number(s.fontWeight)>=700) ? 3 : 4.5;
        if(ratio<limit-.05) issues.push({selector:el.tagName.toLowerCase()+'.'+[...el.classList].join('.'),text:text.slice(0,60),ratio:+ratio.toFixed(2),color:s.color});
    }
    return issues;
}'''

def setup(browser, fixture, width=1440, state=None):
    state = state if state is not None else {}
    page = browser.new_page(viewport={'width': width, 'height': 900})
    page.set_default_timeout(5000)
    errors = []
    page.on('pageerror', lambda e: errors.append(str(e)))
    def route(req):
        path = urlparse(req.request.url).path
        asset = ROOT / 'public' / path.lstrip('/')
        if path in ('/fixture', '/dashboard'):
            req.fulfill(body=fixture.read_text(encoding='utf-8'), content_type='text/html; charset=utf-8')
        elif asset.is_file(): req.fulfill(path=str(asset))
        elif path == '/membership/join':
            state['joins'] = state.get('joins', 0) + 1
            req.fulfill(json=state.get('join', {'success': True, 'data': {'payment': {'transaction_id': 'demo-1', 'qris_url': 'https://pay.example.test/qr.png', 'transaction_expire': 'invalid-date', 'transaction_amount': 27750, 'status': 'PENDING'}}}))
        elif path == '/membership/status': req.fulfill(json={'success': True, 'data': {'is_member': state.get('member', False)}})
        elif path == '/campaign/payment/demo-1': req.fulfill(json={'success': True, 'data': {'status': state.get('payment', 'PENDING')}})
        elif path == '/campaign/payment/qris-download': req.fulfill(path=str(ROOT / 'public/assets/shopads-touch-icon.png'))
        elif path == '/campaign/price-view': req.fulfill(json={'success': True, 'prices': {'sms_lba': None, 'sms_targeted': 100}})
        elif req.request.resource_type in ('stylesheet', 'script', 'image', 'font'): req.fulfill(body='')
        else: req.fulfill(json={'success': True, 'data': {}})
    page.route('**/*', route)
    page.goto('http://ui-audit.test/fixture')
    page.wait_for_timeout(250)
    suggestion = page.locator('button[data-waba-template-suggestion-close]:visible')
    if suggestion.count(): suggestion.first.click()
    return page, errors

def navigation(page, width):
    sidebar = page.locator('#mobile-menu')
    if not sidebar.count(): return
    if width <= 1080:
        page.locator('[data-menu-toggle]').click()
        expect(sidebar).not_to_have_attribute('inert', '')
    selected = sidebar.locator('[aria-current="page"]')
    expect(selected).to_have_count(1)
    destination = selected.get_attribute('href')
    for button in sidebar.locator('[data-nav-toggle]').all():
        was_open = button.get_attribute('aria-expanded') == 'true'
        button.click()
        expect(button).to_have_attribute('aria-expanded', str(not was_open).lower())
        expect(selected).to_have_attribute('href', destination)
        button.click()
    if width <= 1080:
        page.keyboard.press('Escape')
        expect(page.locator('[data-menu-toggle]')).to_have_attribute('aria-expanded', 'false')
        expect(sidebar).to_have_attribute('inert', '')

def membership(browser):
    state = {}
    page, errors = setup(browser, FIXTURES / 'dashboard.html', 390, state)
    page.locator('[data-menu-toggle]').click()
    page.locator('#sidebar-join-membership').click()
    expect(page.locator('#membership-modal')).to_have_attribute('aria-hidden', 'false')
    expect(page.locator('[data-menu-toggle]')).to_have_attribute('aria-expanded', 'false')
    price = page.locator('.membership-modal__price')
    assert price.evaluate('(e)=>e.getBoundingClientRect().height') < 45, 'Price wraps'
    page.locator('#membership-phone').fill('123')
    page.locator('#membership-join-btn').click()
    expect(page.locator('#membership-join-error')).to_be_visible()
    assert state.get('joins', 0) == 0
    page.locator('#membership-phone').fill('081234567890')
    page.locator('#membership-join-btn').click()
    expect(page.locator('#membership-step-qris')).to_be_visible()
    expect(page.locator('#membership-qris-countdown')).not_to_contain_text('NaN')
    page.locator('#membership-modal-close').click()
    page.locator('#btn-join-membership').click()
    expect(page.locator('#membership-step-qris')).to_be_visible()
    assert state['joins'] == 1, 'Reopening created another payment'
    state['payment'] = 'SUCCESS'
    page.locator('#membership-paid-btn').click()
    expect(page.locator('#membership-qris-status')).to_contain_text('Aktivasi membership sedang diproses')
    expect(page.locator('#membership-step-success')).to_be_hidden()
    state['member'] = True
    page.locator('#membership-paid-btn').click()
    expect(page.locator('#membership-step-success')).to_be_visible()
    assert not errors, errors
    page.close()
    for response in ({'success': True, 'data': {}}, {'success': False, 'message': 'Layanan sedang tidak tersedia.'}):
        page, errors = setup(browser, FIXTURES / 'dashboard.html', 390, {'join': response})
        page.locator('#btn-join-membership').click()
        page.locator('#membership-phone').fill('081234567890')
        page.locator('#membership-join-btn').click()
        expect(page.locator('#membership-join-error')).to_be_visible()
        expect(page.locator('#membership-step-success')).to_be_hidden()
        expect(page.locator('#membership-join-btn')).to_be_enabled()
        assert not errors, errors
        page.close()
    print('Membership: validation, sidebar, reopen, pending, paid, activation, API failures passed', flush=True)

def main():
    contrast = {}
    with sync_playwright() as pw:
        browser = pw.chromium.launch()
        for width in (1440, 768, 390):
            for fixture in sorted(FIXTURES.glob('*.html')):
                page, errors = setup(browser, fixture, width)
                assert not page.evaluate('document.documentElement.scrollWidth > innerWidth'), (fixture.stem, width, 'overflow')
                navigation(page, width)
                contrast[f'{fixture.stem}-{width}'] = page.evaluate(CONTRAST)
                (FIXTURES / 'contrast.json').write_text(json.dumps(contrast, indent=2, ensure_ascii=False), encoding='utf-8')
                if fixture.stem == 'dashboard':
                    page.locator('#btn-join-membership').click()
                    contrast[f'membership-{width}'] = page.evaluate(CONTRAST)
                    page.screenshot(path=str(FIXTURES / f'membership-{width}.png'), full_page=True)
                    page.keyboard.press('Escape')
                if fixture.stem in ('dashboard', 'sms-targeted-create', 'sms-targeted-index'):
                    if width <= 1080: page.locator('[data-menu-toggle]').click()
                    page.screenshot(path=str(FIXTURES / f'{fixture.stem}-{width}.png'), full_page=True)
                assert not errors, (fixture.stem, errors)
                print(f'{fixture.stem} {width}px: layout, navigation, JavaScript passed', flush=True)
                page.close()
        membership(browser)
        browser.close()
    (FIXTURES / 'contrast.json').write_text(json.dumps(contrast, indent=2, ensure_ascii=False), encoding='utf-8')
    unique = {item['selector']: item for items in contrast.values() for item in items}
    print('Contrast candidates:', json.dumps(unique, ensure_ascii=True), flush=True)

if __name__ == '__main__': main()
