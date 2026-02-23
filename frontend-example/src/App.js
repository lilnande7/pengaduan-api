import React, { useState } from 'react';
import axios from 'axios';

// API Base URL
const API_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

// Setup axios defaults
axios.defaults.baseURL = API_URL;
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

const TicketForm = () => {
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    category: 'Layanan Publik',
    message: '',
    evidence_file: null
  });
  
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState(null);
  const [categories] = useState([
    'Layanan Publik',
    'Infrastruktur',
    'Keamanan',
    'Lingkungan',
    'Kesehatan',
    'Pendidikan',
    'Transportasi',
    'Lainnya'
  ]);

  const handleChange = (e) => {
    const { name, value, files } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: files ? files[0] : value
    }));
  };

  const submitTicket = async (e) => {
    e.preventDefault();
    setLoading(true);
    setResult(null);

    try {
      const submitData = new FormData();
      Object.keys(formData).forEach(key => {
        if (formData[key] !== null && formData[key] !== '') {
          submitData.append(key, formData[key]);
        }
      });

      const response = await axios.post('/tickets', submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });

      setResult({ 
        success: true, 
        message: response.data.message,
        ticket: response.data.data.ticket 
      });
      
      // Reset form
      setFormData({
        name: '',
        phone: '',
        email: '',
        category: 'Layanan Publik',
        message: '',
        evidence_file: null
      });
      
      // Reset file input
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = '';

    } catch (error) {
      setResult({ 
        success: false, 
        message: error.response?.data?.message || 'Terjadi kesalahan',
        errors: error.response?.data?.errors
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
      <h2 className="text-2xl font-bold mb-6 text-center text-gray-800">
        📝 Form Pengaduan Online
      </h2>
      
      {result && (
        <div className={`mb-4 p-4 rounded-md ${result.success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
          <p className="font-medium">{result.message}</p>
          {result.ticket && (
            <p className="mt-2 text-sm">
              <strong>Nomor Tiket:</strong> {result.ticket.ticket_number}
            </p>
          )}
          {result.errors && (
            <ul className="mt-2 text-sm">
              {Object.values(result.errors).flat().map((error, index) => (
                <li key={index}>• {error}</li>
              ))}
            </ul>
          )}
        </div>
      )}

      <form onSubmit={submitTicket} className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Nama Lengkap *
          </label>
          <input
            type="text"
            name="name"
            value={formData.name}
            onChange={handleChange}
            required
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Masukkan nama lengkap"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Nomor Telepon *
          </label>
          <input
            type="tel"
            name="phone"
            value={formData.phone}
            onChange={handleChange}
            required
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="08123456789"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Email
          </label>
          <input
            type="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="email@example.com (opsional)"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Kategori Pengaduan *
          </label>
          <select
            name="category"
            value={formData.category}
            onChange={handleChange}
            required
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            {categories.map(cat => (
              <option key={cat} value={cat}>{cat}</option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Pesan Pengaduan *
          </label>
          <textarea
            name="message"
            value={formData.message}
            onChange={handleChange}
            required
            rows="4"
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Tuliskan detail pengaduan Anda (minimal 10 karakter)"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Bukti (Foto/Dokumen)
          </label>
          <input
            type="file"
            name="evidence_file"
            onChange={handleChange}
            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <p className="text-xs text-gray-500 mt-1">
            Format: JPG, PNG, PDF, DOC, DOCX (max 5MB)
          </p>
        </div>

        <button
          type="submit"
          disabled={loading}
          className="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {loading ? '⏳ Mengirim...' : '📤 Kirim Pengaduan'}
        </button>
      </form>
      
      <div className="mt-6 p-4 bg-gray-50 rounded-md">
        <h3 className="font-medium text-gray-800 mb-2">ℹ️ Informasi:</h3>
        <ul className="text-sm text-gray-600 space-y-1">
          <li>• Anda akan mendapat nomor tiket setelah mengirim pengaduan</li>
          <li>• Admin akan menerima notifikasi WhatsApp</li>
          <li>• Gunakan nomor tiket untuk mengecek status pengaduan</li>
        </ul>
      </div>
    </div>
  );
};

// Ticket Status Checker Component
const TicketStatusChecker = () => {
  const [checkData, setCheckData] = useState({
    ticket_number: '',
    phone: ''
  });
  const [loading, setLoading] = useState(false);
  const [ticket, setTicket] = useState(null);
  const [error, setError] = useState('');

  const handleChange = (e) => {
    setCheckData(prev => ({
      ...prev,
      [e.target.name]: e.target.value
    }));
  };

  const checkStatus = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setTicket(null);

    try {
      const response = await axios.post('/tickets/check-status', checkData);
      setTicket(response.data.data);
    } catch (error) {
      setError(error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
      setLoading(false);
    }
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'baru': return 'bg-yellow-100 text-yellow-800';
      case 'diproses': return 'bg-blue-100 text-blue-800';
      case 'selesai': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md mt-8">
      <h2 className="text-2xl font-bold mb-6 text-center text-gray-800">
        🔍 Cek Status Tiket
      </h2>

      <form onSubmit={checkStatus} className="space-y-4">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Nomor Tiket
          </label>
          <input
            type="text"
            name="ticket_number"
            value={checkData.ticket_number}
            onChange={handleChange}
            required
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="TCK-20260223-0001"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-1">
            Nomor Telepon
          </label>
          <input
            type="tel"
            name="phone"
            value={checkData.phone}
            onChange={handleChange}
            required
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="08123456789"
          />
        </div>

        <button
          type="submit"
          disabled={loading}
          className="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
        >
          {loading ? '⏳ Mengecek...' : '🔍 Cek Status'}
        </button>
      </form>

      {error && (
        <div className="mt-4 p-4 bg-red-100 text-red-700 rounded-md">
          {error}
        </div>
      )}

      {ticket && (
        <div className="mt-6 space-y-4">
          <div className="p-4 bg-gray-50 rounded-md">
            <h3 className="font-bold text-lg text-gray-800 mb-3">Detail Tiket</h3>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-sm text-gray-600">Nomor Tiket:</p>
                <p className="font-medium">{ticket.ticket_number}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Status:</p>
                <span className={`inline-block px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(ticket.status)}`}>
                  {ticket.status.toUpperCase()}
                </span>
              </div>
              <div>
                <p className="text-sm text-gray-600">Kategori:</p>
                <p className="font-medium">{ticket.category}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600">Tanggal:</p>
                <p className="font-medium">{new Date(ticket.created_at).toLocaleString('id-ID')}</p>
              </div>
            </div>
            <div className="mt-4">
              <p className="text-sm text-gray-600">Pesan:</p>
              <p className="mt-1">{ticket.message}</p>
            </div>
          </div>

          {ticket.replies && ticket.replies.length > 0 && (
            <div className="p-4 bg-blue-50 rounded-md">
              <h4 className="font-bold text-gray-800 mb-3">
                💬 Balasan ({ticket.replies.length})
              </h4>
              <div className="space-y-3">
                {ticket.replies.map((reply, index) => (
                  <div key={index} className="p-3 bg-white rounded-md border-l-4 border-blue-400">
                    <div className="flex justify-between items-start mb-2">
                      <span className="font-medium text-blue-800">
                        👨‍💼 {reply.admin?.name} (Admin)
                      </span>
                      <span className="text-xs text-gray-500">
                        {new Date(reply.created_at).toLocaleString('id-ID')}
                      </span>
                    </div>
                    <p>{reply.message}</p>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

// Main App Component
const App = () => {
  return (
    <div className="min-h-screen bg-gray-100 py-8">
      <div className="container mx-auto px-4">
        <h1 className="text-3xl font-bold text-center mb-8 text-gray-800">
          🏛️ Sistem Pengaduan Online
        </h1>
        <TicketForm />
        <TicketStatusChecker />
      </div>
    </div>
  );
};

export default App;