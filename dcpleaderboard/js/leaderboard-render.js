/*the function should always return a category
if the record has no value in last year status and in this years status
it should go to Serie D
*/

function ti_status_to_category(status, defaultCategory = 'D'){
    var mapping = new Map([ ['', 'D'], ['D', 'C'], ['S', 'B'], ['P', 'A'] ]);
    return !mapping.has(status)? defaultCategory : mapping.get(status);
}

function calculate_category(row) {
    const category = ti_status_to_category(row.ti_status_last_year);
    const newCategory = ti_status_to_category(row.ti_status, 'A');
    
    return {
        name: newCategory < category ? newCategory : category,
        promoted: newCategory < category
    };
}

function render_category(row) {
    var category = calculate_category(row);
    return `
        <div class="progress-cell">
            <div class="progress-text" >${(category.promoted ? '<span class="promotion-marker">&#9650;</span>' : '<span class="promotion-marker">&nbsp;</span>')}
                Serie ${category.name}
            </div>
        </div>
    `;
}

function populate_areas (data , $, table) {
                    var areaSelect = $('#areaFilter');
                    areaSelect.empty().append('<option value="">All Areas</option>');
                    data.forEach(function (area) {
                        areaSelect.append('<option value="' + area + '">' + area + '</option>');
                    });
                    table.draw();
                }

// Interpolate between #006094 and #004165
function interpolateColor(startHex, endHex, factor) {
    const hexToRgb = hex => [
        parseInt(hex.slice(1, 3), 16),
        parseInt(hex.slice(3, 5), 16),
        parseInt(hex.slice(5, 7), 16)
    ];

    const rgbToHex = rgb => '#' + rgb.map(val => {
        const hex = Math.round(val).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
    }).join('');

    const startRGB = hexToRgb(startHex);
    const endRGB = hexToRgb(endHex);

    const resultRGB = startRGB.map((startVal, i) =>
        startVal + (endRGB[i] - startVal) * factor
    );

    return rgbToHex(resultRGB);
}

