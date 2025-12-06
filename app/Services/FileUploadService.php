<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload a file and return the file path.
     */
    public function upload(UploadedFile $file, string $directory = 'uploads', ?string $disk = 'public'): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::random(40) . '.' . $extension;
        $filePath = $file->storeAs($directory, $fileName, $disk);
        
        return [
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    /**
     * Upload an avatar image.
     */
    public function uploadAvatar(UploadedFile $file, int $userId): string
    {
        $directory = 'avatars/' . $userId;
        $result = $this->upload($file, $directory);
        return $result['file_path'];
    }

    /**
     * Upload a post attachment.
     */
    public function uploadAttachment(UploadedFile $file, int $postId = 0): array
    {
        // If postId is 0, use temp directory (for files uploaded before post creation)
        $directory = $postId > 0 ? 'attachments/' . $postId : 'attachments/temp';
        return $this->upload($file, $directory);
    }

    /**
     * Move temporary attachment to post directory.
     */
    public function moveAttachmentToPost(string $tempFilePath, int $postId): string
    {
        $newDirectory = 'attachments/' . $postId;
        $fileName = basename($tempFilePath);
        $newPath = $newDirectory . '/' . $fileName;
        
        // Create directory if it doesn't exist
        Storage::disk('public')->makeDirectory($newDirectory);
        
        // Move file
        if (Storage::disk('public')->exists($tempFilePath)) {
            Storage::disk('public')->move($tempFilePath, $newPath);
            return $newPath;
        }
        
        return $tempFilePath;
    }

    /**
     * Delete a file.
     */
    public function delete(string $filePath, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }
        return false;
    }

    /**
     * Validate image file.
     */
    public function validateImage(UploadedFile $file, int $maxSize = 2048): bool
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSizeKB = $maxSize * 1024; // Convert to KB
        
        return in_array($file->getMimeType(), $allowedMimes) 
            && $file->getSize() <= $maxSizeKB;
    }

    /**
     * Validate attachment file.
     */
    public function validateAttachment(UploadedFile $file, int $maxSize = 10240): bool
    {
        // Allow image files: jpeg, png, gif, webp
        // Also allow document files: pdf, doc, docx, xls, xlsx, zip
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'application/x-zip-compressed'
        ];
        $maxSizeKB = $maxSize * 1024; // Convert to KB
        
        return in_array($file->getMimeType(), $allowedMimes) 
            && $file->getSize() <= $maxSizeKB;
    }
}

