<div
    x-data="profilePhotoPoc()"
    x-on:livewire-upload-error.window="handleUploadError()"
    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 md:p-8"
>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase text-teal-700 tracking-wide">Lokaler Test</p>
        <h1 class="text-2xl font-extrabold text-gray-900 mt-1">Foto Upload PoC</h1>
        <p class="text-sm text-gray-500 mt-2">Bitte laden Sie ein aktuelles Porträtfoto hoch, keine Ausweise oder Dokumente.</p>
    </div>

    <input
        x-ref="cameraInput"
        x-on:change="handleFileSelect($event)"
        type="file"
        accept="image/*"
        capture="user"
        class="hidden"
    >

    <input
        x-ref="galleryInput"
        x-on:change="handleFileSelect($event)"
        type="file"
        accept="image/jpeg,image/png,image/webp,image/*"
        class="hidden"
    >

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button
            type="button"
            x-on:click="$refs.cameraInput.click()"
            class="min-h-12 rounded-lg bg-teal-600 px-4 py-3 text-sm font-semibold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500"
        >
            Mit Kamera aufnehmen
        </button>
        <button
            type="button"
            x-on:click="$refs.galleryInput.click()"
            class="min-h-12 rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500"
        >
            Foto auswählen
        </button>
    </div>

    <template x-if="error">
        <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" x-text="error"></div>
    </template>

    <template x-if="mode === 'crop'">
        <div class="mt-6 space-y-4">
            <div class="rounded-xl border border-gray-200 bg-gray-950 overflow-hidden">
                <div x-ref="cropperHost" class="photo-poc-cropper-host">
                    <img
                        x-ref="sourceImage"
                        x-bind:src="sourceUrl"
                        x-on:load="initCropper()"
                        x-on:error="setError('Das Foto konnte im Browser nicht angezeigt werden. Sie können ohne Foto fortfahren oder eine andere Datei wählen.')"
                        alt=""
                        class="max-w-full"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <button
                    type="button"
                    x-on:click="applyCrop()"
                    x-bind:disabled="isUploading"
                    class="min-h-11 rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700 disabled:opacity-50"
                >
                    <span x-show="!isUploading">Übernehmen</span>
                    <span x-show="isUploading">Upload <span x-text="uploadProgress"></span>%</span>
                </button>
                <button
                    type="button"
                    x-on:click="$refs.galleryInput.click()"
                    x-bind:disabled="isUploading"
                    class="min-h-11 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                >
                    Anderes Foto
                </button>
                <button
                    type="button"
                    x-on:click="removePhoto()"
                    x-bind:disabled="isUploading"
                    class="min-h-11 rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50 disabled:opacity-50"
                >
                    Foto entfernen
                </button>
            </div>
        </div>
    </template>

    <div x-show="mode === 'preview'" x-cloak class="mt-6">
            <div class="flex flex-col sm:flex-row gap-5 sm:items-center">
                <img x-bind:src="previewUrl" alt="" class="h-40 w-40 rounded-xl object-cover border border-gray-200 shadow-sm">
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900">Zugeschnittenes JPEG ist bei Livewire angekommen.</p>
                    <p class="text-xs text-gray-500 mt-1">Der PoC speichert noch kein Foto am Mitglied. Das passiert erst in der vollständigen Formularintegration.</p>

                    @if ($photoResult)
                        <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-600">
                            <dt class="font-medium">MIME</dt>
                            <dd>{{ $photoResult['mime'] }}</dd>
                            <dt class="font-medium">Größe</dt>
                            <dd>{{ number_format($photoResult['size'] / 1024, 1, ',', '.') }} KB</dd>
                            <dt class="font-medium">Pixel</dt>
                            <dd>{{ $photoResult['width'] }} x {{ $photoResult['height'] }}</dd>
                        </dl>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button
                    type="button"
                    x-on:click="$refs.galleryInput.click()"
                    class="min-h-11 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Anderes Foto
                </button>
                <button
                    type="button"
                    x-on:click="removePhoto()"
                    class="min-h-11 rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                >
                    Foto entfernen
                </button>
            </div>
    </div>

    @error('croppedPhoto')
        <p class="mt-4 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
