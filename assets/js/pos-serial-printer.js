/**
 * POS Bluetooth Thermal Printer Engine - Web Serial API (ESC/POS Native)
 * Khusus Printer Portable 58mm (Rongta RPP02N, Zjiang, dsb) via Bluetooth SPP (COM Port)
 * 
 * Keunggulan:
 * 1. 100% Tajam & Pekat (Murni Teks ESC/POS Hardware Font, Bukan Gambar GDI Raster).
 * 2. Transmisi secepat kilat (hanya ~150 bytes per tiket).
 * 3. Auto-remember port: Staf hanya pair 1x, seterusnya otomatis print tanpa dialog!
 */

class PosSerialPrinter {
    constructor() {
        this.port = null;
        this.writer = null;
        this.isConnected = false;
        this.baudRate = 9600; // Standar Bluetooth SPP
    }

    // Cek apakah browser mendukung Web Serial API
    isSupported() {
        return 'serial' in navigator;
    }

    // Inisialisasi otomatis: Cek apakah ada port yang sudah pernah diizinkan sebelumnya
    async autoConnect() {
        if (!this.isSupported()) return false;
        try {
            const ports = await navigator.serial.getPorts();
            if (ports && ports.length > 0) {
                this.port = ports[0];
                this.isConnected = true;
                return true;
            }
        } catch (err) {
            console.warn('[POS-Serial] Auto-connect check failed:', err);
        }
        return false;
    }

    // Minta user memilih port Bluetooth COM (hanya perlu 1x klik)
    async requestPort() {
        if (!this.isSupported()) {
            throw new Error('Browser Anda tidak mendukung Web Serial API. Harap gunakan Google Chrome atau Microsoft Edge terbaru.');
        }

        try {
            this.port = await navigator.serial.requestPort();
            this.isConnected = true;
            return true;
        } catch (err) {
            console.warn('[POS-Serial] User membatalkan pemilihan port:', err);
            return false;
        }
    }

    // Buka port, kirim buffer bytes, lalu tutup port secara aman
    async sendRaw(byteArray) {
        if (!this.port) {
            const ok = await this.requestPort();
            if (!ok) return false;
        }

        try {
            // Buka koneksi jika belum terbuka
            if (!this.port.readable || !this.port.writable) {
                await this.port.open({ 
                    baudRate: this.baudRate,
                    dataBits: 8,
                    stopBits: 1,
                    parity: "none",
                    flowControl: "none" // Penting: none agar tidak terblokir hardware handshake!
                });
            }

            const writer = this.port.writable.getWriter();
            await writer.write(byteArray);
            writer.releaseLock();
            
            // Beri jeda 100ms sebelum menutup port agar buffer terkirim tuntas lewat Bluetooth
            await new Promise(resolve => setTimeout(resolve, 150));
            await this.port.close();
            return true;
        } catch (err) {
            console.error('[POS-Serial] Gagal menulis ke port printer:', err);
            // Reset port jika terjadi error agar bisa re-open
            try { await this.port.close(); } catch(e){}
            throw err;
        }
    }

    // Konversi string teks ke Uint8Array (ASCII/CP437)
    textToBytes(text) {
        const bytes = [];
        for (let i = 0; i < text.length; i++) {
            bytes.push(text.charCodeAt(i) & 0xFF);
        }
        return bytes;
    }

    /**
     * Buat paket data ESC/POS murni untuk tiket antrean 58mm (32 kolom)
     */
    buildTicketPayload(ticket, schoolName) {
        const ESC = 0x1B;
        const GS  = 0x1D;
        const LF  = 0x0A;

        let b = [];

        // 1. Inisialisasi printer
        b.push(ESC, 0x40);

        // 2. Select Character Code Table (CP437)
        b.push(ESC, 0x74, 0x00);

        // 3. Header Sekolah (Center, Bold, Normal Size)
        b.push(ESC, 0x61, 0x01); // Center
        b.push(ESC, 0x45, 0x01); // Bold On
        b.push(...this.textToBytes((schoolName || 'SMK MUHAMMADIYAH BANDONGAN').toUpperCase() + '\n'));
        b.push(ESC, 0x45, 0x00); // Bold Off
        b.push(...this.textToBytes('Pelayanan Tata Usaha & Adm\n'));

        // 4. Garis Pembatas (32 karakter untuk printer 58mm)
        b.push(...this.textToBytes('================================\n'));

        // 5. Label Nomor Antrean
        b.push(ESC, 0x45, 0x01); // Bold
        b.push(...this.textToBytes('NOMOR ANTREAN ANDA\n\n'));

        // 6. Nomor Antrean RAKSASA (Double Width + Double Height)
        b.push(GS, 0x21, 0x22); // 3x Height, 3x Width
        b.push(...this.textToBytes((ticket.ticket_number || 'A-001') + '\n'));
        b.push(GS, 0x21, 0x00); // Reset ke Normal
        b.push(LF);

        // 7. Nama Layanan & Loket (Double Width)
        b.push(GS, 0x21, 0x10); // Double Height
        b.push(...this.textToBytes((ticket.service_name || 'Layanan').toUpperCase() + '\n'));
        b.push(GS, 0x21, 0x00); // Reset

        b.push(ESC, 0x45, 0x01); // Bold
        b.push(...this.textToBytes('MENUJU LOKET ' + (ticket.loket_number || 1) + '\n'));
        b.push(ESC, 0x45, 0x00); // Bold Off

        // 8. Garis Pembatas
        b.push(...this.textToBytes('================================\n'));

        // 9. Meta Info Tanggal & Waktu (Rata Kiri/Format Tabel 32 Kolom)
        b.push(ESC, 0x61, 0x00); // Rata Kiri
        b.push(ESC, 0x45, 0x01); // Bold

        const tgl = ticket.date_str || new Date().toLocaleDateString('id-ID');
        const wkt = (ticket.time_str || new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'})) + ' WIB';
        const sisa = (ticket.waiting_before !== undefined ? ticket.waiting_before : '0') + ' Orang';

        // Helper format baris 32 kolom: "Label:             Nilai"
        const formatRow = (label, val) => {
            const spaceCount = 32 - (label.length + val.length);
            const spaces = ' '.repeat(Math.max(1, spaceCount));
            return label + spaces + val + '\n';
        };

        b.push(...this.textToBytes(formatRow('Tanggal:', tgl)));
        b.push(...this.textToBytes(formatRow('Waktu Ambil:', wkt)));
        b.push(...this.textToBytes(formatRow('Antrean di Depan:', sisa)));
        b.push(ESC, 0x45, 0x00); // Bold off

        // 10. Garis Pembatas Halus
        b.push(...this.textToBytes('--------------------------------\n'));

        // 11. Footer (Center)
        b.push(ESC, 0x61, 0x01); // Center
        b.push(...this.textToBytes('Simpan tiket ini & perhatikan\nlayar monitor ruang tunggu.\n'));
        b.push(...this.textToBytes('Terima kasih telah tertib antre.\n'));

        // 12. Feed & Cut (Untuk printer portable: Feed 3 baris)
        b.push(ESC, 0x64, 0x04); // Feed 4 baris agar mudah disobek
        
        return new Uint8Array(b);
    }

    // API Eksekusi Cetak Tiket
    async printTicket(ticket, schoolName) {
        const payload = this.buildTicketPayload(ticket, schoolName);
        return await this.sendRaw(payload);
    }
}

// Inisialisasi Singleton Global
window.posPrinter = new PosSerialPrinter();
