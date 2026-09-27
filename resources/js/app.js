import './bootstrap';

import Alpine from 'alpinejs';
import {
    Activity, ArrowLeft, ArrowRight, ArrowUpRight, Bell, BrainCircuit,
    Calculator, ChartNoAxesCombined, Flame, Gauge, HeartPulse, Info,
    LayoutDashboard, LockKeyhole, LogOut, Menu, NotebookPen, PlusCircle,
    Salad, ScanLine, ScrollText, Settings, ShieldCheck, Sparkles,
    Stethoscope, UserPlus, UserRound, Users, Utensils, X, createIcons,
} from 'lucide';

window.Alpine = Alpine;
window.Healo = {
    chart: async () => (await import('chart.js/auto')).default,
    dataTable: async () => {
        await import('datatables.net-dt/css/dataTables.dataTables.css');
        return (await import('datatables.net-dt')).default;
    },
    alert: async () => (await import('sweetalert2')).default,
};

Alpine.start();

const renderIcons = () => createIcons({
    icons: {
        Activity, ArrowLeft, ArrowRight, ArrowUpRight, Bell, BrainCircuit,
        Calculator, ChartNoAxesCombined, Flame, Gauge, HeartPulse, Info,
        LayoutDashboard, LockKeyhole, LogOut, Menu, NotebookPen, PlusCircle,
        Salad, ScanLine, ScrollText, Settings, ShieldCheck, Sparkles,
        Stethoscope, UserPlus, UserRound, Users, Utensils, X,
    },
});

document.addEventListener('DOMContentLoaded', renderIcons);
document.addEventListener('livewire:navigated', renderIcons);
