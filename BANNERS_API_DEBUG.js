// Test script untuk debugging banners API
// Buka browser console dan chạy command này:

// 1. Test create banner
fetch('/PetsAccessories/admin/backend/api/banners/create.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        title: 'Test Banner',
        image_url: 'test.jpg',
        link_url: '/test',
        status: 1
    })
})
.then(r => r.text())
.then(text => {
    console.log('Response type:', text.substring(0, 50));
    console.log('Full response:', text);
    try {
        const json = JSON.parse(text);
        console.log('Parsed JSON:', json);
    } catch(e) {
        console.error('JSON Parse Error:', e);
    }
});

// 2. Test list
fetch('/PetsAccessories/admin/backend/api/banners/list.php')
.then(r => r.text())
.then(text => {
    console.log('List response:', text);
    try {
        const json = JSON.parse(text);
        console.log('Parsed:', json);
    } catch(e) {
        console.error('Parse error:', e);
    }
});
