// Translation data
const homeUrl = 'https://poezia.us/registration';
const translations = {
    ru: {
        title: 'Присоединяйтесь к Берлинскому Поэтическому Форуму',
        subtitle: 'Знакомьтесь с поэтами и любителями литературы',
        firstName: 'Имя *',
        lastName: 'Фамилия *',
        email: 'Email адрес *',
        whatsapp: 'Номер WhatsApp *',
        city: 'Город *',
        country: 'Страна *',
        submitBtn: 'Присоединиться к форуму',
        submitting: 'Регистрация...',
        privacyTitle: 'Уведомление о конфиденциальности:',
        privacyText: 'Ваша информация будет использоваться исключительно для деятельности Берлинского Поэтического Форума и не будет передаваться третьим лицам.',

        // Validation messages
        nameRequired: 'Это поле обязательно для заполнения',
        nameMinLength: 'Должно быть не менее 2 символов',
        nameInvalidChars: 'Разрешены только буквы, пробелы, дефисы и апострофы',
        emailRequired: 'Email обязателен',
        emailInvalid: 'Пожалуйста, введите действительный email адрес',
        phoneRequired: 'Номер WhatsApp обязателен',
        phoneInvalid: 'Пожалуйста, введите действительный номер телефона',
        emailGood: 'Email выглядит хорошо!',

        // Success/Error messages
        registrationSuccess: 'Регистрация успешна! Добро пожаловать в Берлинский Поэтический Форум!',
        fixErrors: 'Пожалуйста, исправьте ошибки перед отправкой',
        serverError: 'Что-то пошло не так. Пожалуйста, попробуйте еще раз.',
        connectionError: 'Не удается подключиться к серверу. Пожалуйста, проверьте соединение.'
    },
    en: {
        title: 'Join Berlin Poetry Forum',
        subtitle: 'Connect with fellow poets and literary enthusiasts',
        firstName: 'First Name *',
        lastName: 'Last Name *',
        email: 'Email Address *',
        whatsapp: 'WhatsApp Number *',
        city: 'City *',
        country: 'Country *',
        submitBtn: 'Join the Forum',
        submitting: 'Registering...',
        privacyTitle: 'Privacy Notice:',
        privacyText: 'Your information will be used solely for Berlin Poetry Forum activities and will not be shared with third parties.',

        // Validation messages
        nameRequired: 'This field is required',
        nameMinLength: 'Must be at least 2 characters',
        nameInvalidChars: 'Only letters, spaces, hyphens and apostrophes allowed',
        emailRequired: 'Email is required',
        emailInvalid: 'Please enter a valid email address',
        phoneRequired: 'WhatsApp number is required',
        phoneInvalid: 'Please enter a valid phone number',
        emailGood: 'Email looks good!',

        // Success/Error messages
        registrationSuccess: 'Registration successful! Welcome to Berlin Poetry Forum!',
        fixErrors: 'Please fix the errors before submitting',
        serverError: 'Something went wrong. Please try again.',
        connectionError: 'Unable to connect to server. Please check your connection.'
    }
};

class RegistrationForm {
    constructor() {
        this.form = document.getElementById('registrationForm');
        this.loading = document.getElementById('loading');
        this.btnText = document.getElementById('btnText');
        this.submitBtn = this.form.querySelector('.submit-btn');
        this.currentLang = 'ru';

        this.validators = {
            firstName: this.validateName,
            lastName: this.validateName,
            email: this.validateEmail,
            whatsapp: this.validateWhatsApp,
            city: this.validateName,
            country: this.validateName
        };

        this.init();
    }

    init() {
        this.form.addEventListener('submit', this.handleSubmit.bind(this));
        this.initLanguageSwitcher();

        // Real-time validation
        Object.keys(this.validators).forEach(fieldName => {
            const field = document.getElementById(fieldName);
            field.addEventListener('blur', () => this.validateField(fieldName));
            field.addEventListener('input', () => this.clearErrors(fieldName));
        });

        // Set initial language
        this.setLanguage('ru');
    }

    initLanguageSwitcher() {
        const flagButtons = document.querySelectorAll('.flag-btn');
        flagButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const lang = btn.dataset.lang;
                this.setLanguage(lang);
            });
        });
    }

    setLanguage(lang) {
        if (this.currentLang === lang) return;

        // Add fade effect
        const container = document.querySelector('.container');
        container.classList.add('fade-transition', 'fade-out');

        setTimeout(() => {
            this.currentLang = lang;

            // Update active flag
            document.querySelectorAll('.flag-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.lang === lang);
            });

            // Update document language
            document.documentElement.lang = lang;

            // Update title
            document.title = lang === 'ru' ?
                'Берлинский Поэтический Форум - Регистрация' :
                'Berlin Poetry Forum - Registration';

            // Translate all elements
            document.querySelectorAll('[data-translate]').forEach(element => {
                const key = element.dataset.translate;
                if (translations[lang][key]) {
                    element.textContent = translations[lang][key];
                }
            });

            // Update placeholder
            document.getElementById('whatsapp').placeholder = '+49 123 456 7890';

            // Remove fade effect
            container.classList.remove('fade-out');
        }, 150);
    }

    t(key) {
        return translations[this.currentLang][key] || key;
    }

    validateName(value) {
        if (!value.trim()) return this.t('nameRequired');
        if (value.trim().length < 2) return this.t('nameMinLength');
        if (!/^[a-zA-ZÀ-ÿа-яА-Я\s'-]+$/.test(value)) return this.t('nameInvalidChars');
        return null;
    }

    validateEmail(value) {
        if (!value.trim()) return this.t('emailRequired');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            return this.t('emailInvalid');
        }
        return null;
    }

    validateWhatsApp(value) {
        if (!value.trim()) return this.t('phoneRequired');

        const cleaned = value.replace(/[\s\-().]/g, ''); // Remove spaces, dashes, parentheses, dots
        const phoneRegex = /^\+?\d{10,15}$/; // Allow + and 10–15 digits

        if (!phoneRegex.test(cleaned)) {
            return this.t('phoneInvalid');
        }
        return null;
    }

    validateField(fieldName) {
        const field = document.getElementById(fieldName);
        const value = field.value;
        const validator = this.validators[fieldName];
        const error = validator.call(this, value);

        this.displayFieldError(fieldName, error);

        if (error) {
            field.classList.add('error');
            field.classList.remove('success');
        } else {
            field.classList.remove('error');
            field.classList.add('success');

            if (fieldName === 'email') {
                document.getElementById('emailSuccess').textContent = this.t('emailGood');
                document.getElementById('emailSuccess').style.display = 'block';
            }
        }

        return !error;
    }

    displayFieldError(fieldName, error) {
        const errorElement = document.getElementById(fieldName + 'Error');
        if (error) {
            errorElement.textContent = error;
            errorElement.style.display = 'block';
        } else {
            errorElement.style.display = 'none';
        }
    }

    clearErrors(fieldName) {
        const field = document.getElementById(fieldName);
        field.classList.remove('error');
        document.getElementById(fieldName + 'Error').style.display = 'none';

        if (fieldName === 'email') {
            document.getElementById('emailSuccess').style.display = 'none';
        }
    }

    validateForm() {
        let isValid = true;
        Object.keys(this.validators).forEach(fieldName => {
            if (!this.validateField(fieldName)) {
                isValid = false;
            }
        });
        return isValid;
    }

    async handleSubmit(e) {
        e.preventDefault();

        if (!this.validateForm()) {
            this.showNotification(this.t('fixErrors'), 'error');
            return;
        }

        this.setLoading(true);

        try {
            const formData = new FormData(this.form);
            formData.append('language', this.currentLang);

            const response = await this.submitToServer(formData);

            if (response.success) {
                this.showNotification(this.t('registrationSuccess'), 'success');
                this.form.reset();
                this.clearAllValidation();
            } else {
                throw new Error(response.message || this.t('serverError'));
            }
        } catch (error) {
            let errorMessage = this.t('serverError');
            if (error.message.includes('connect') || error.message.includes('fetch')) {
                errorMessage = this.t('connectionError');
            }
            this.showNotification(errorMessage, 'error');
        } finally {
            this.setLoading(false);
        }
    }

    async submitToServer(formData) {
        try {
            const response = await fetch(`${homeUrl}/api/register_json.php`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Server error occurred');
            }

            return data;
        } catch (error) {
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error('Unable to connect to server. Please check your connection.');
            }
            throw error;
        }
    }

    setLoading(isLoading) {
        if (isLoading) {
            this.loading.style.display = 'inline-block';
            this.btnText.textContent = this.t('submitting');
            this.submitBtn.disabled = true;
        } else {
            this.loading.style.display = 'none';
            this.btnText.textContent = this.t('submitBtn');
            this.submitBtn.disabled = false;
        }
    }

    clearAllValidation() {
        Object.keys(this.validators).forEach(fieldName => {
            const field = document.getElementById(fieldName);
            field.classList.remove('error', 'success');
            document.getElementById(fieldName + 'Error').style.display = 'none';
        });
        document.getElementById('emailSuccess').style.display = 'none';
    }

    showNotification(message, type) {
        // Remove existing notifications
        const existing = document.querySelector('.notification');
        if (existing) existing.remove();

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => notification.classList.add('show'), 100);
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
}

// Initialize the form when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new RegistrationForm();
});