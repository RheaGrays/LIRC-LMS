@push('scripts')
<script>
    const COLLEGE_YEAR_LEVELS = ["1st Year", "2nd Year", "3rd Year", "4th Year", "5th Year"];
    const JHS_YEAR_LEVELS     = ["Grade 7", "Grade 8", "Grade 9", "Grade 10"];
    const SHS_YEAR_LEVELS     = ["Grade 11", "Grade 12"];

    function registrationApp() {
        return {
            departmentsData: [],
            patronCategories: [],
            patronCategoriesLoaded: false,
            registeredId: null,

            step: 'info',
            currentTime: '',
            submitting: false,
            submitError: '',

            // Photo state
            capturedImage: null,
            photoTaken: false,
            photoSyncMode: 'mobile',
            photoSyncSessionId: null,
            photoSyncOwnerToken: null,
            photoSyncMobileUrl: null,
            photoSyncPollTimer: null,

            steps: [
                { id: "info",    label: "Patron Info", num: 1 },
                { id: "photo",   label: "Photo",       num: 2 },
                { id: "confirm", label: "Confirm",     num: 3 },
            ],

            form: {
                patronCategory: '',
                studentId: '',
                lastName: '',
                firstName: '',
                middleName: '',
                level: '',
                college: '',
                department: '',
                yearLevel: '',
                email: '',
            },

            errors: {},

            get stepIdx() {
                return this.steps.findIndex(s => s.id === this.step);
            },

            get isStudent() {
                return this.form.patronCategory === 'Student';
            },

            get isVisitor() {
                return this.form.patronCategory === 'Visitor';
            },

            get collegeOptions() {
                if (!this.form.level) return [];
                return this.departmentsData.filter(d => d.level === this.form.level).map(d => d.name).sort();
            },

            get programOptions() {
                if (!this.form.college) return [];
                const dept = this.departmentsData.find(d => d.level === this.form.level && d.name === this.form.college);
                if (!dept || !dept.programs) return [];
                return dept.programs.map(p => p.name).sort();
            },

            get yearOptions() {
                if (!this.form.level) return [];
                if (this.form.level === 'basic_ed') {
                    if (/junior/i.test(this.form.college)) return JHS_YEAR_LEVELS;
                    if (/senior/i.test(this.form.college)) return SHS_YEAR_LEVELS;
                    return ["Grade 7","Grade 8","Grade 9","Grade 10","Grade 11","Grade 12"];
                }
                if (this.form.department) {
                    const dept = this.departmentsData.find(d => d.level === this.form.level && d.name === this.form.college);
                    if (dept) {
                        const prog = dept.programs.find(p => p.name === this.form.department);
                        if (prog && prog.years) {
                            return Array.from({ length: prog.years }, (_, i) => {
                                const n = i + 1;
                                const s = ["st","nd","rd"][n - 1] || "th";
                                return `${n}${s} Year`;
                            });
                        }
                    }
                }
                return COLLEGE_YEAR_LEVELS;
            },

            async init() {
                this.updateTime();
                setInterval(() => this.updateTime(), 30000);

                // CSRF Token Keepalive (Refreshes token every 4 mins to prevent 419 Page Expired)
                setInterval(async () => {
                    try {
                        const tokenRes = await fetch('/csrf-token');
                        if (tokenRes.ok) {
                            const tokenData = await tokenRes.json();
                            if (tokenData.token) {
                                document.querySelector('meta[name="csrf-token"]').content = tokenData.token;
                            }
                        }
                    } catch (e) {}
                }, 240000);

                // Fetch academics and patron categories in parallel
                const [acadRes, catRes] = await Promise.allSettled([
                    fetch('/api/academics'),
                    fetch('/api/patron-categories'),
                ]);

                if (acadRes.status === 'fulfilled' && acadRes.value.ok) {
                    this.departmentsData = await acadRes.value.json();
                }
                if (catRes.status === 'fulfilled' && catRes.value.ok) {
                    this.patronCategories = await catRes.value.json();
                } else {
                    this.patronCategories = ['Student','Employee','Post Graduate','Alumni','Visitor'];
                }
                this.patronCategoriesLoaded = true;
            },

            updateTime() {
                this.currentTime = new Date().toLocaleTimeString("en-PH", { hour: "2-digit", minute: "2-digit" });
            },

            onCategoryChange() {
                this.errors.patronCategory = '';
                // Clear academic fields when switching from Student
                this.form.level = '';
                this.form.college = '';
                this.form.department = '';
                this.form.yearLevel = '';
                this.errors.level = '';
                this.errors.college = '';
                this.errors.department = '';
                this.errors.yearLevel = '';
            },

            onLevelChange() {
                this.errors.level = '';
                this.form.college = '';
                this.form.department = '';
                this.form.yearLevel = '';
                this.errors.college = '';
                this.errors.department = '';
                this.errors.yearLevel = '';
            },

            onCollegeChange() {
                this.errors.college = '';
                this.form.department = '';
                this.errors.department = '';
                this.form.yearLevel = '';
                this.errors.yearLevel = '';
            },

            async checkPatronId() {
                this.errors.studentId = '';
            },

            validateInfo() {
                const e = {};

                if (!this.form.patronCategory) e.patronCategory = "Please select a patron category";

                if (!this.isVisitor && !this.form.studentId.trim()) {
                    e.studentId = "ID Number is required";
                }

                if (!this.form.lastName.trim())  e.lastName  = "Last name is required";
                if (!this.form.firstName.trim()) e.firstName = "First name is required";

                if (this.isStudent) {
                    if (!this.form.level)      e.level    = "Please select a level";
                    if (!this.form.college)    e.college  = this.form.level === "basic_ed" ? "Please select a department" : "Please select a college";
                    if (this.form.level === "college" && !this.form.department) e.department = "Please select a program";
                    if (!this.form.yearLevel)  e.yearLevel = "Please select a year level";
                }

                if (this.form.email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                    e.email = "Invalid email address";
                }

                this.errors = e;
                return Object.keys(e).length === 0;
            },

            async startPhotoSyncSession() {
                this.stopPhotoSyncPolling();
                try {
                    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const res = await fetch('/api/register/photo-session/create', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.photoSyncSessionId = data.session_id;
                        this.photoSyncOwnerToken = data.owner_token;
                        this.photoSyncMobileUrl = data.mobile_url;
                        this.photoSyncPollTimer = setInterval(() => this.checkPhotoSyncSession(), 1500);

                        this.$nextTick(() => {
                            const canvas = document.getElementById('qrcode-canvas');
                            if (canvas && window.QRCode) {
                                window.QRCode.toCanvas(canvas, this.photoSyncMobileUrl, {
                                    width: 180,
                                    margin: 2,
                                    color: { dark: '#0f2744', light: '#ffffff' }
                                });
                            }
                        });
                    }
                } catch (e) {}
            },

            async checkPhotoSyncSession() {
                if (!this.photoSyncSessionId || this.capturedImage) return;
                try {
                    const res = await fetch(`/api/register/photo-session/check/${this.photoSyncSessionId}`);
                    if (res.ok) {
                        const data = await res.json();
                        if (data.status === 'completed') {
                            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            const consumeRes = await fetch(`/api/register/photo-session/consume`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    session_id: this.photoSyncSessionId,
                                    owner_token: this.photoSyncOwnerToken
                                })
                            });
                            
                            if (consumeRes.ok) {
                                const consumeData = await consumeRes.json();
                                if (consumeData.status === 'completed' && consumeData.photoDataUrl) {
                                    this.capturedImage = consumeData.photoDataUrl;
                                    this.photoTaken = true;
                                    this.stopPhotoSyncPolling();
                                }
                            }
                        }
                    }
                } catch (e) {}
            },

            stopPhotoSyncPolling() {
                if (this.photoSyncPollTimer) {
                    clearInterval(this.photoSyncPollTimer);
                    this.photoSyncPollTimer = null;
                }
            },

            handleBack() {
                if (this.step === 'info') {
                    window.location.href = "{{ route('kiosk.index') }}";
                } else if (this.step === 'photo') {
                    this.stopPhotoSyncPolling();
                    this.step = 'info';
                } else if (this.step === 'confirm') {
                    this.step = 'photo';
                    if (!this.capturedImage) this.startPhotoSyncSession();
                }
            },

            async handleNext() {
                if (this.step === 'info') {
                    if (this.validateInfo()) {
                        this.step = 'photo';
                        this.startPhotoSyncSession();
                    }
                } else if (this.step === 'photo') {
                    this.stopPhotoSyncPolling();
                    this.step = 'confirm';
                } else if (this.step === 'confirm') {
                    this.submitForm();
                }
            },

            async submitForm() {
                this.submitting = true;
                this.submitError = '';

                let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

                try {
                    const response = await fetch("{{ route('register.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ...this.form,
                            photoDataUrl: this.capturedImage
                        })
                    });

                    if (response.status === 419) {
                        try {
                            const freshTokenRes = await fetch('/csrf-token');
                            if (freshTokenRes.ok) {
                                const freshData = await freshTokenRes.json();
                                document.querySelector('meta[name="csrf-token"]').content = freshData.token;
                            }
                        } catch (e) {}
                        
                        this.submitError = "Your security token expired. We refreshed it — please click 'Submit Registration' again!";
                        return;
                    }

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.registeredId = data.id || this.form.studentId;
                        this.step = 'done';
                    } else {
                        if (data.errors) {
                            if (data.errors.studentId) {
                                this.errors.studentId = "This ID is already registered.";
                                this.step = 'info';
                            } else {
                                this.submitError = "Please check your inputs.";
                                this.step = 'info';
                                Object.keys(data.errors).forEach(key => {
                                    this.errors[key] = data.errors[key][0];
                                });
                            }
                        } else {
                            this.submitError = data.message || "Registration failed. Please try again.";
                        }
                    }
                } catch (err) {
                    this.submitError = "A network error occurred. Please try again.";
                } finally {
                    this.submitting = false;
                }
            }
        };
    }
</script>
@endpush
