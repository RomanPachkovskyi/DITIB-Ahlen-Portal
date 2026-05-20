import Cropper from 'cropperjs';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('profilePhotoPoc', (config = {}) => ({
        cropper: null,
        error: '',
        isUploading: false,
        mode: config.mode || 'idle',
        previewUrl: config.previewUrl || '',
        sourceUrl: '',
        uploadProgress: 0,

        handleFileSelect(event) {
            const file = event.target.files?.[0];
            event.target.value = '';

            if (!file) {
                return;
            }

            this.error = '';

            if (file.type && !file.type.startsWith('image/')) {
                this.setError('Bitte wählen Sie eine Bilddatei aus.');

                return;
            }

            if (file.size > 12 * 1024 * 1024) {
                this.setError('Das Foto ist für die lokale Browser-Verarbeitung zu groß. Bitte wählen Sie ein kleineres Foto oder fahren Sie ohne Foto fort.');

                return;
            }

            this.destroyCropper();
            this.revokeUrl('sourceUrl');
            this.revokeUrl('previewUrl');
            this.sourceUrl = URL.createObjectURL(file);
            this.mode = 'crop';
        },

        initCropper() {
            if (!this.$refs.sourceImage || !this.$refs.cropperHost || this.cropper) {
                return;
            }

            try {
                this.cropper = new Cropper(this.$refs.sourceImage, {
                    container: this.$refs.cropperHost,
                    template: [
                        '<cropper-canvas background>',
                        '<cropper-image rotatable scalable skewable translatable></cropper-image>',
                        '<cropper-shade hidden></cropper-shade>',
                        '<cropper-selection initial-coverage="0.75" aspect-ratio="1" initial-aspect-ratio="1" movable resizable>',
                        '<cropper-grid role="grid" bordered covered></cropper-grid>',
                        '<cropper-crosshair centered></cropper-crosshair>',
                        '<cropper-handle action="move" theme-color="rgba(255, 255, 255, 0.35)"></cropper-handle>',
                        '<cropper-handle action="n-resize"></cropper-handle>',
                        '<cropper-handle action="e-resize"></cropper-handle>',
                        '<cropper-handle action="s-resize"></cropper-handle>',
                        '<cropper-handle action="w-resize"></cropper-handle>',
                        '<cropper-handle action="ne-resize"></cropper-handle>',
                        '<cropper-handle action="nw-resize"></cropper-handle>',
                        '<cropper-handle action="se-resize"></cropper-handle>',
                        '<cropper-handle action="sw-resize"></cropper-handle>',
                        '</cropper-selection>',
                        '</cropper-canvas>',
                    ].join(''),
                });
            } catch (error) {
                this.setError('Das Foto konnte im Browser nicht zum Zuschneiden vorbereitet werden. Sie können ohne Foto fortfahren oder eine andere Datei wählen.');
            }
        },

        async applyCrop() {
            if (!this.cropper || this.isUploading) {
                return;
            }

            this.error = '';
            this.isUploading = true;
            this.uploadProgress = 0;

            try {
                const selection = this.cropper.getCropperSelection();

                if (!selection) {
                    throw new Error('Missing crop selection.');
                }

                const canvas = await selection.$toCanvas({
                    width: 800,
                    height: 800,
                });

                const blob = await new Promise((resolve, reject) => {
                    canvas.toBlob((result) => {
                        if (result) {
                            resolve(result);
                        } else {
                            reject(new Error('Canvas export failed.'));
                        }
                    }, 'image/jpeg', 0.85);
                });

                if (blob.size > 1024 * 1024) {
                    throw new Error('Output file too large.');
                }

                const file = new File([blob], 'profile-photo-poc.jpg', {
                    type: 'image/jpeg',
                    lastModified: Date.now(),
                });

                this.revokeUrl('previewUrl');
                this.previewUrl = URL.createObjectURL(blob);

                this.$wire.upload(
                    'croppedPhoto',
                    file,
                    async () => {
                        await this.$wire.acceptCroppedPhoto();
                        this.destroyCropper();
                        this.revokeUrl('sourceUrl');
                        this.mode = 'preview';
                        this.isUploading = false;
                        this.uploadProgress = 100;
                    },
                    () => {
                        this.handleUploadError();
                    },
                    (event) => {
                        this.uploadProgress = event.detail?.progress ?? 0;
                    },
                );
            } catch (error) {
                const message = error.message === 'Output file too large.'
                    ? 'Das zugeschnittene Foto ist größer als 1 MB. Bitte wählen Sie einen kleineren Ausschnitt oder ein anderes Foto.'
                    : 'Das Foto konnte nicht verarbeitet werden. Sie können ohne Foto fortfahren oder eine andere Datei wählen.';

                this.setError(message);
                this.isUploading = false;
            }
        },

        async removePhoto() {
            this.destroyCropper();
            this.revokeUrl('sourceUrl');
            this.revokeUrl('previewUrl');
            this.error = '';
            this.isUploading = false;
            this.mode = 'idle';
            this.uploadProgress = 0;
            await this.$wire.removeCroppedPhoto();
        },

        handleUploadError() {
            this.setError('Das zugeschnittene Foto konnte nicht an Livewire übergeben werden. Sie können ohne Foto fortfahren oder eine andere Datei wählen.');
            this.isUploading = false;
        },

        setError(message) {
            this.error = message;
            this.isUploading = false;
        },

        destroyCropper() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
        },

        revokeUrl(property) {
            if (this[property]) {
                if (this[property].startsWith('blob:')) {
                    URL.revokeObjectURL(this[property]);
                }

                this[property] = '';
            }
        },
    }));
});
