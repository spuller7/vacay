class Datatable {

    static generateHtml(data) {
        let html = '';
        data.forEach(row => {
            html += '<tr>';

            for (const col in row) {
                html += '<td>' + row[col] + '</td>';
            }

            html += '</tr>';
        });

        return html;
    }
}