@extends('layouts.admin')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1>Log Kehadiran</h1>
        <p style="color: #64748b;">Lihat record untuk: <strong id="selected-date-text">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</strong></p>
    </div>
    
    <div class="export-actions" style="display: flex; gap: 10px;">
        <button onclick="exportToExcel()" class="btn-export-excel" style="background: #10b981; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);">
            📊 Export Excel
        </button>
        <button onclick="exportToPDF()" class="btn-export-pdf" style="background: #ef4444; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);">
            📄 Export PDF
        </button>
    </div>
    
    <form action="{{ route('admin.attendance.index') }}" method="GET" class="filter-form">
        <input type="date" name="date" value="{{ $selectedDate }}" class="date-input">
        <button type="submit" class="btn-filter">Filter</button>
        <a href="{{ route('admin.attendance.index') }}" class="btn-reset">Hari ini</a>
    </form>
</div>

<div class="card">
    <table id="attendance-table">
        <thead>
            <tr>
                <th>Karyawan</th>
                <th>Clock In</th>
                <th>Clock Out</th>
                <th>Lokasi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                @php
                    $record = $employee->attendances->first();
                @endphp
            <tr>
                <td>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{{ $employee->name }}</strong>
                        <small style="color: #94a3b8;">{{ $employee->nip }}</small>
                    </div>
                </td>
                
                @if($record)
                    <td>{{ $record->clock_in->format('H:i') }}</td>
                    <td>{{ $record->clock_out ? $record->clock_out->format('H:i') : '--:--' }}</td>
                    <td>
                        @if($record->status == 'masuk')
                            {{ $record->address }}
                        @else
                            <span style="color: #64748b; font-style: italic;">({{ ucfirst($record->status) }}) {{ $record->notes }}</span>
                        @endif
                    </td>
                    <td>
                        @if($record->status == 'masuk')
                            <span class="dot {{ $record->is_late ? 'dot-red' : 'dot-green' }}"></span>
                            {{ $record->is_late ? 'TELAT' : 'TEPAT' }}
                        @else
                            <span class="dot" style="background-color: #eab308;"></span>
                            {{ ucfirst($record->status) }}
                        @endif
                    </td>
                @else
                    <!-- JIKA TIDAK MASUK / BELUM ABSEN -->
                    <td style="color: #cbd5e1; font-style: italic;">--:--</td>
                    <td style="color: #cbd5e1; font-style: italic;">--:--</td>
                    <td style="color: #94a3b8; font-style: italic;">Belum ada record lokasi</td>
                    <td>
                        <span class="status-pill" style="background: #f1f5f9; color: #64748b;">
                            ALFA / BELUM ABSEN
                        </span>
                    </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                    Belum ada data karyawan di sistem.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- DATA EXPORT SCRIPTS -->
<script>
    function getCleanTableData() {
        const rows = document.querySelectorAll("#attendance-table tbody tr");
        const data = [];
        
        rows.forEach(row => {
            const nameTd = row.cells[0];
            if (!nameTd || nameTd.colSpan > 1) return;

            const name = nameTd.getAttribute('data-name');
            const nip = nameTd.getAttribute('data-nip');
            const clockIn = row.cells[1].innerText;
            const clockOut = row.cells[2].innerText;
            const lokasi = row.cells[3].innerText;
            const status = row.cells[4].innerText.trim();

            data.push({
                "Nama Karyawan": name,
                "NIP": nip,
                "Clock In": clockIn,
                "Clock Out": clockOut,
                "Alamat Lokasi": lokasi,
                "Status": status
            });
        });
        return data;
    }

    function exportToExcel() {
        const data = getCleanTableData();
        if(data.length === 0) return alert("Tidak ada data untuk diexport!");

        const dateText = document.getElementById('selected-date-text').innerText;
        const worksheet = XLSX.utils.json_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        
        XLSX.utils.book_append_sheet(workbook, worksheet, "Attendance Log");
        
        // Auto-fit column widths nicely inside Excel lines
        const maxProps = [{wch: 25}, {wch: 15}, {wch: 10}, {wch: 10}, {wch: 50}, {wch: 12}];
        worksheet['!cols'] = maxProps;

        XLSX.writeFile(workbook, `Log_Kehadiran_${dateText.replace(/ /g, '_')}.xlsx`);
    }

    function exportToPDF() {
        const rawData = getCleanTableData();
        if(rawData.length === 0) return alert("Tidak ada data untuk diexport!");

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4'); // Landscape layout configuration for long addresses
        const dateText = document.getElementById('selected-date-text').innerText;

        doc.setFont("Helvetica", "bold");
        doc.setFontSize(18);
        doc.text("FORTUNET - LOG KEHADIRAN KARYAWAN", 14, 18);
        
        doc.setFont("Helvetica", "normal");
        doc.setFontSize(11);
        doc.setTextColor(100);
        doc.text(`Tanggal Rekam Log: ${dateText}`, 14, 25);

        const bodyData = rawData.map(item => [
            `${item["Nama Karyawan"]}\n(NIP: ${item["NIP"]})`,
            item["Clock In"],
            item["Clock Out"],
            item["Alamat Lokasi"],
            item["Status"]
        ]);

        doc.autoTable({
            startY: 30,
            head: [['Karyawan', 'Clock In', 'Clock Out', 'Lokasi Absensi', 'Status']],
            body: bodyData,
            theme: 'striped',
            headStyles: { fillColor: [99, 102, 241], fontStyle: 'bold' }, // Clean theme matching dashboard
            styles: { fontSize: 9, cellPadding: 4 },
            columnStyles: {
                0: { cellWidth: 45 },
                1: { cellWidth: 20 },
                2: { cellWidth: 20 },
                3: { cellWidth: 150 }, // Gives maximum horizontal space to reverse geocoding addresses
                4: { cellWidth: 25, halign: 'center' }
            }
        });

        doc.save(`Log_Kehadiran_${dateText.replace(/ /g, '_')}.pdf`);
    }
</script>
@endsection