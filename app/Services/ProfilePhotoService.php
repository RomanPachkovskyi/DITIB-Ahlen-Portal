<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Exceptions\ImageException;
use Intervention\Image\Exceptions\MissingDependencyException;
use Intervention\Image\ImageManager;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfilePhotoService
{
    private const DISK = 'member_photos';

    private const ROOT_DIRECTORY = 'member-photos';

    private const OUTPUT_SIZE = 800;

    private const JPEG_QUALITY = 85;

    private const MAX_INPUT_KILOBYTES = 8192;

    private const MAX_OUTPUT_BYTES = 1048576;

    public function store(Member $member, UploadedFile $file): string
    {
        $this->validateInput($file);

        if (blank($member->member_number)) {
            throw new RuntimeException('Profile photos require an issued member number.');
        }

        $disk = Storage::disk(self::DISK);
        $directory = $this->directoryFor($member);
        $path = $this->pathFor($member);
        $temporaryPath = $directory.'/.tmp-'.bin2hex(random_bytes(8)).'.jpg';
        $absoluteDirectory = $disk->path($directory);
        $absoluteTemporaryPath = $disk->path($temporaryPath);
        $absoluteFinalPath = $disk->path($path);

        if (! is_dir($absoluteDirectory) && ! mkdir($absoluteDirectory, 0755, true) && ! is_dir($absoluteDirectory)) {
            throw new RuntimeException("Could not create profile photo directory [{$directory}].");
        }

        try {
            $manager = new ImageManager(new Driver);
            $image = $manager
                ->decodePath($file->getRealPath())
                ->orient()
                ->cover(self::OUTPUT_SIZE, self::OUTPUT_SIZE);

            $image
                ->encode(new JpegEncoder(quality: self::JPEG_QUALITY, strip: true))
                ->save($absoluteTemporaryPath);

            clearstatcache(true, $absoluteTemporaryPath);

            if ((filesize($absoluteTemporaryPath) ?: 0) > self::MAX_OUTPUT_BYTES) {
                throw ValidationException::withMessages([
                    'profile_photo' => 'Das optimierte Foto darf maximal 1 MB groß sein.',
                ]);
            }

            $oldPath = $member->profile_photo_path;

            if (! rename($absoluteTemporaryPath, $absoluteFinalPath)) {
                throw new RuntimeException("Could not move profile photo to [{$path}].");
            }

            if ($oldPath !== null && $oldPath !== $path) {
                $disk->delete($oldPath);
            }

            $member->forceFill([
                'profile_photo_path' => $path,
                'profile_photo_uploaded_at' => now(),
            ])->save();

            return $path;
        } catch (MissingDependencyException $exception) {
            $disk->delete($temporaryPath);

            throw $exception;
        } catch (ImageException $exception) {
            $disk->delete($temporaryPath);

            throw ValidationException::withMessages([
                'profile_photo' => 'Das Foto konnte nicht verarbeitet werden. Bitte verwenden Sie JPEG, PNG oder WebP.',
            ]);
        } catch (ValidationException $exception) {
            $disk->delete($temporaryPath);

            throw $exception;
        } catch (RuntimeException $exception) {
            $disk->delete($temporaryPath);

            throw $exception;
        }
    }

    public function delete(Member $member): void
    {
        if ($member->profile_photo_path !== null) {
            Storage::disk(self::DISK)->delete($member->profile_photo_path);
        }

        $member->forceFill([
            'profile_photo_path' => null,
            'profile_photo_uploaded_at' => null,
            'profile_photo_zustimmung' => false,
            'profile_photo_zustimmung_at' => null,
        ])->save();
    }

    public function response(Member $member): StreamedResponse
    {
        $path = $member->profile_photo_path;

        abort_if($path === null || ! Storage::disk(self::DISK)->exists($path), 404);

        return Storage::disk(self::DISK)->response($path, null, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function pathFor(Member $member): string
    {
        $memberNumber = $member->member_number;

        if (blank($memberNumber)) {
            throw new RuntimeException('Profile photos require an issued member number.');
        }

        return $this->directoryFor($member)."/{$memberNumber}-profile.jpg";
    }

    private function directoryFor(Member $member): string
    {
        $memberNumber = $member->member_number;

        if (blank($memberNumber)) {
            throw new RuntimeException('Profile photos require an issued member number.');
        }

        return self::ROOT_DIRECTORY."/{$memberNumber}";
    }

    private function validateInput(UploadedFile $file): void
    {
        Validator::make(
            ['profile_photo' => $file],
            [
                'profile_photo' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:'.self::MAX_INPUT_KILOBYTES,
                ],
            ],
            [
                'profile_photo.image' => 'Bitte verwenden Sie eine Bilddatei.',
                'profile_photo.mimes' => 'Bitte verwenden Sie JPEG, PNG oder WebP.',
                'profile_photo.max' => 'Das Foto darf maximal 8 MB groß sein.',
            ],
        )->validate();
    }
}
