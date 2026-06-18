@extends('layouts.admin')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<div class="page-header" style="margin-bottom: 25px;">
    <h1>📊 Laporan Ringkasan Kehadiran</h1>
    <p style="color: #64748b;">Kalkulasi matriks ketidakhadiran dan saldo potongan periode: <strong id="pdf-periode">{{ $label }}</strong></p>
</div>

<!-- FILTER PANEL -->
<div class="card" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
    <form action="{{ route('admin.reports.attendance') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        
        <div style="display: flex; flex-direction: column; gap: 5px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Penyaringan Berdasarkan</label>
            <select name="type" style="padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
                <option value="day" {{ $type === 'day' ? 'selected' : '' }}>Harian</option>
                <option value="week" {{ $type === 'week' ? 'selected' : '' }}>Mingguan</option>
                <option value="month" {{ $type === 'month' ? 'selected' : '' }}>Bulanan</option>
            </select>
        </div>

        <div style="display: flex; flex-direction: column; gap: 5px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Pilih Tanggal</label>
            <input type="date" name="date" value="{{ $targetDate }}" style="padding: 9px; border-radius: 6px; border: 1px solid #cbd5e1;">
        </div>

        <button type="submit" style="background: #4f46e5; color: white; border: none; padding: 11px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">
            📊 Hitung Rekap
        </button>

        <button type="button" onclick="exportAttendanceExcel()" style="background: #10b981; color: white; border: none; padding: 11px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">
            📊 Export Excel
        </button>
        
        <button type="button" onclick="exportAttendancePDF()" style="background: #ef4444; color: white; border: none; padding: 11px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">
            📄 Export PDF
        </button>
    </form>
</div>

<!-- MATRIKS TABEL UTAMA -->
<div class="card" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden;">
    <table id="attendance-summary-table" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <th style="padding: 14px;">Nama Staff</th>
                <th style="padding: 14px;">Divisi</th>
                <th style="padding: 14px; text-align: center;">Hadir</th>
                <th style="padding: 14px; text-align: center;">Telat</th>
                <th style="padding: 14px; text-align: center;">Cuti</th>
                <th style="padding: 14px; text-align: center;">Izin/Sakit</th>
                <th style="padding: 14px; text-align: center;">Alpha</th>
                <th style="padding: 14px; text-align: right;">Total Potongan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $data)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 14px;">
                    <div style="display:flex; flex-direction:column;">
                        <strong style="color: #1e293b;">{{ $data['name'] }}</strong>
                        <small style="color: #94a3b8;">{{ $data['nip'] }}</small>
                    </div>
                </td>
                <td style="padding: 14px; color: #475569;">{{ $data['division'] }}</td>
                <td style="padding: 14px; text-align: center; font-weight: 600; color: #10b981;">{{ $data['hadir'] }}</td>
                <td style="padding: 14px; text-align: center; font-weight: 600; color: #ef4444;">{{ $data['telat'] }}</td>
                <td style="padding: 14px; text-align: center; color: #f59e0b;">{{ $data['cuti'] }}</td>
                <td style="padding: 14px; text-align: center; color: #6366f1;">{{ $data['izin'] }}</td>
                <td style="padding: 14px; text-align: center; font-weight: 600; color: #b91c1c; background: #fef2f2;">{{ $data['alpha'] }}</td>
                <td style="padding: 14px; text-align: right; font-weight: 700; color: #b91c1c;">
                    Rp {{ number_format($data['potongan'], 0, ',', '.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function exportAttendanceExcel() {
        const table = document.getElementById("attendance-summary-table");
        const workbook = XLSX.utils.table_to_book(table, {sheet: "Rekap Kehadiran"});
        XLSX.writeFile(workbook, "Rekap_Kehadiran_Staff.xlsx");
    }

    function exportAttendancePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4'); // Landscape layout
        const periode = document.getElementById('pdf-periode').innerText;

        doc.setFont("Helvetica", "bold");
        doc.setFontSize(15);
        doc.text("LAPORAN RINGKASAN REKAPITULASI KEHADIRAN KARYAWAN", 14, 18);
        
        doc.setFontSize(11);
        doc.setFont("Helvetica", "normal");
        doc.text(`Periode Evaluasi: ${periode}`, 14, 25);

        doc.autoTable({
            startY: 32,
            html: '#attendance-summary-table',
            theme: 'grid',
            headStyles: { fillColor: [31, 41, 55] },
            styles: { fontSize: 9 }
        });

        doc.save(`Rekap_Kehadiran_${periode.replace(/ /g, '_')}.pdf`);
    }
</script>
@endsection