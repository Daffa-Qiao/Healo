import './bootstrap';
import './echo';

import Alpine from 'alpinejs';
import {
    Activity, AlertTriangle, ArrowLeft, ArrowRight, ArrowUpRight, Bell, BrainCircuit,
    Calculator, CalendarCheck2, CalendarDays, Camera, ChartNoAxesCombined, Check, ChevronDown, ChevronRight, Circle, CircleCheck, Clock, Droplets, Flame, Footprints, Gauge, HeartPulse, Image, Info, Languages, Lightbulb,
    LayoutDashboard, LockKeyhole, LogOut, Menu, MessageCircleHeart, MessageCircleWarning, NotebookPen,
    PanelLeftClose, PanelLeftOpen, Pencil, Plus, PlusCircle, Salad, Save, ScanLine, ScrollText, Search, Settings,
    Send, ShieldCheck, Smile, Sparkles, Stethoscope, Target, Trash2, TriangleAlert, Upload, UserPlus, UserRound, UserRoundCheck, Users, Utensils, Weight, X, createIcons,
} from 'lucide';

window.Alpine = Alpine;
window.Healo = {
    chart: async () => (await import('chart.js/auto')).default,
    dataTable: async () => {
        await import('datatables.net-dt/css/dataTables.dataTables.css');
        return (await import('datatables.net-dt')).default;
    },
    alert: async () => (await import('sweetalert2')).default,
    notificationCenter: (initialItems, initialCount, userId) => ({
        sidebarOpen: false,
        sidebarCollapsed: localStorage.getItem('healo-sidebar') === 'collapsed',
        notificationOpen: false,
        languageOpen: false,
        userOpen: false,
        notifications: initialItems,
        unreadCount: initialCount,
        init() {
            if (!window.Echo || !userId) return;
            window.Echo.private(`users.${userId}.notifications`)
                .listen('.notification.created', ({ notification }) => {
                    this.notifications = [notification, ...this.notifications.filter((item) => item.id !== notification.id)].slice(0, 5);
                    this.unreadCount += 1;
                    window.Healo.toast(notification.title, notification.message);
                });
        },
    }),
    toast: async (title, text) => {
        const Swal = (await import('sweetalert2')).default;
        Swal.fire({ toast: true, position: 'top-end', icon: 'info', title, text, showConfirmButton: false, timer: 4500, timerProgressBar: true });
    },
};

Alpine.start();

const renderIcons = () => createIcons({
    icons: {
        Activity, AlertTriangle, ArrowLeft, ArrowRight, ArrowUpRight, Bell, BrainCircuit,
        Calculator, CalendarCheck2, CalendarDays, Camera, ChartNoAxesCombined, Check, ChevronDown, ChevronRight, Circle, CircleCheck, Clock, Droplets, Flame, Footprints, Gauge, HeartPulse, Image, Info, Languages, Lightbulb,
        LayoutDashboard, LockKeyhole, LogOut, Menu, MessageCircleHeart, MessageCircleWarning, NotebookPen,
        PanelLeftClose, PanelLeftOpen, Pencil, Plus, PlusCircle, Salad, Save, ScanLine, ScrollText, Search, Settings,
        Send, ShieldCheck, Smile, Sparkles, Stethoscope, Target, Trash2, TriangleAlert, Upload, UserPlus, UserRound, UserRoundCheck, Users, Utensils, Weight, X,
    },
});

document.addEventListener('DOMContentLoaded', renderIcons);
document.addEventListener('livewire:navigated', renderIcons);
