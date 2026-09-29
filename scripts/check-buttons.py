"""Browser regression checks against rendered Blade fixtures (Python Playwright)."""
import json
import re
import sys
from pathlib import Path
from urllib.parse import urlparse
from playwright.sync_api import sync_playwright, expect

ROOT = Path(__file__).resolve().parents[1]

def check_sms(page, targeted):
    cards = page.locator('[data-button-card]')
    expect(cards).to_have_count(1)
    page.locator('#campaign_message').fill('Promo hari ini')
    url = cards.first.locator('[data-action-url]')
    expect(url).to_be_editable()
    url.fill('https://example.com/promo')
    expect(page.locator('#campaign_message')).to_have_value('Promo hari ini https://example.com/promo')
    url.fill('https://example.com/katalog')
    expect(page.locator('#campaign_message')).to_have_value('Promo hari ini https://example.com/katalog')
    page.locator('#campaignAddButton').click()
    expect(cards).to_have_count(2)
    expect(page.locator('#campaignAddButton')).to_be_disabled()
    cards.first.locator('[data-remove-button]').click()
    expect(cards).to_have_count(1)
    expect(page.locator('#campaign_message')).to_have_value('Promo hari ini')
    expect(page.locator('#campaignAddButton')).to_be_enabled()
    page.locator('#campaign_sender').fill('SHOPADS')
    if not targeted:
        page.locator('#campaignOpenLocationModal').click()
        expect(page.locator('#campaignMapModal')).to_be_visible()
        page.locator('button[data-location-close]').first.click()
        expect(page.locator('#campaignMapModal')).to_be_hidden()
        page.locator('#campaignOpenLocationModal').click()
        page.evaluate("Object.assign(window.campaignLocationState, {name: 'Jakarta', lat: -6.2, lng: 106.8})")
        page.locator('#campaignApplyLocation').click()
    page.locator('#campaignStepOneNext').click()
    if targeted:
        page.locator('#campaignStepTwoNext').click()
    page.locator('#campaignAddSchedule').click()
    expect(page.locator('[data-schedule-item]')).to_have_count(2)
    page.locator('[data-remove-schedule]').last.click()
    expect(page.locator('[data-schedule-item]')).to_have_count(1)
    page.locator('[data-time-trigger]').first.click()
    expect(page.locator('[data-time-panel]').first).to_be_visible()
    page.locator('[data-time-trigger]').first.click()
    expect(page.locator('[data-time-panel]').first).to_be_hidden()
    page.locator('#campaignTestRecipientInput').fill('081234567890')
    page.locator('#campaignTestRecipientAdd').click()
    expect(page.locator('[data-test-recipient]')).to_have_count(1)
    page.locator('.campaign-test-recipient__remove').click()
    expect(page.locator('[data-test-recipient]')).to_have_count(0)
    page.locator('#campaignTestRecipientInput').fill('081234567890')
    page.locator('#campaignTestRecipientAdd').click()
    page.locator('#campaignRecipientCount').fill('1000')
    page.locator('#campaignTermsCheck').check()
    page.locator('#campaignStepThreeNext' if targeted else '#campaignStepTwoNext').click()
    page.locator('[data-review-toggle="content"]').click()
    expect(page.locator('[data-review-body="content"]')).to_be_visible()
    page.locator('[data-review-toggle="content"]').click()
    expect(page.locator('[data-review-body="content"]')).to_be_hidden()
    page.locator('[data-review-edit="1"]').first.click()
    expect(page.locator('#campaignAddButton')).to_be_visible()

def check_template(page):
    page.locator('#templateAddButton').click()
    card = page.locator('[data-template-button-card]').first
    card.locator('[data-template-button-action]').select_option('URL')
    url = card.locator('[data-template-button-url-suffix]')
    expect(url).to_be_editable()
    url.fill('https://example.com/promo')
    expect(card.locator('[data-template-button-url]')).to_have_value('https://example.com/promo')
    card.locator('[data-template-button-action]').select_option('QUICK_REPLY')
    expect(card.locator('[data-template-button-url]')).to_have_value('')
    for _ in range(9):
        page.locator('#templateAddButton').click()
    expect(page.locator('#templateAddButton')).to_be_disabled()
    card.locator('[data-template-button-remove]').click()
    expect(page.locator('#templateAddButton')).to_be_enabled()

def run():
    with sync_playwright() as pw:
        browser = pw.chromium.launch()
        for fixture in sorted((ROOT / 'storage/app/button-check').glob('*.html')):
            page = browser.new_page()
            page.set_default_timeout(4000)
            errors = []
            page.on('pageerror', lambda error: errors.append(str(error)))
            def route(request):
                path = request.request.url.split('?', 1)[0]
                if path.endswith('/fixture'):
                    request.fulfill(body=fixture.read_text(encoding='utf-8'), content_type='text/html')
                elif request.request.resource_type in ('script', 'stylesheet', 'image', 'font'):
                    asset = ROOT / 'public' / urlparse(path).path.lstrip('/')
                    if asset.is_file():
                        request.fulfill(path=str(asset))
                    else:
                        request.fulfill(body='')
                else:
                    request.fulfill(json={'success': True, 'data': {}})
            page.route('**/*', route)
            page.goto('http://button-check.test/fixture')
            page.wait_for_timeout(150)
            print(f'{fixture.stem}: {json.dumps(errors)}', flush=True)
            if '--audit' not in sys.argv:
                assert not errors, errors
                if fixture.stem in ('campaign-lba', 'campaign-targeted'):
                    check_sms(page, fixture.stem == 'campaign-targeted')
                elif fixture.stem == 'campaign-wa-template':
                    check_template(page)
                assert not errors, errors
                print(f'{fixture.stem}: interactions passed', flush=True)
            page.close()
        browser.close()

if __name__ == '__main__':
    run()
