<?php

namespace Telegram\Traits;

use Telegram\Response;
use Telegram\Support\InputFile;

trait Replies
{
    use BotApiMethods;

    /**
     * Possible `$action` values (default `typing`):
     * - `typing` for text messages,
     * - `upload_photo` for photos,
     * - `record_video` or `upload_video` for videos,
     * - `record_voice` or `upload_voice` for voice notes,
     * - `upload_document` for general files,
     * - `choose_sticker` for stickers,
     * - `find_location` for location data,
     * - `record_video_note` or `upload_video_note` for video notes.
     *
     * @return Response
     */
    public function replyWithChatAction(string $action = 'typing')
    {
        return $this->sendChatAction($this->getChatId(), $action);
    }

    // /**
    //  * @TODO
    //  * @return Response
    //  */
    // public function replyWithReply(string|int $messageId, $text = '', $keyboard = null, $extra = [])
    // {
    //     return $this->sendReply($this->getChatId(), $messageId, $text, $keyboard, $extra);
    // }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithText(string $text, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->sendMessage($this->getChatId(), $text, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithForwardMessage(string|int $fromChatId, string|int $messageId, array $extra = [])
    {
        return $this->forwardMessage($this->getChatId(), $fromChatId, $messageId, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithCopyMessage(string|int $fromChatId, string|int $messageId, array $extra = [])
    {
        return $this->copyMessage($this->getChatId(), $fromChatId, $messageId, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithPhoto(string|InputFile $photo, string $caption = '', string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendPhoto($this->getChatId(), $photo, $caption, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithAudio(string|InputFile $audio, string $caption = '', string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendAudio($this->getChatId(), $audio, $caption, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithDocument(string|InputFile $document, string $caption = '', string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendDocument($this->getChatId(), $document, $caption, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithAnimation(string|InputFile $animation, string $caption = '', string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendAnimation($this->getChatId(), $animation, $caption, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithVideo(string|InputFile $video, string $caption = '', string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendVideo($this->getChatId(), $video, $caption, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithVideoNote(string|InputFile $videoNote, string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendVideoNote($this->getChatId(), $videoNote, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithSticker(string|InputFile $sticker, string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendSticker($this->getChatId(), $sticker, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithVoice(string|InputFile $voice, string $caption = '', string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendVoice($this->getChatId(), $voice, $caption, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithMediaGroup(array $media, $extra = [])
    {
        return $this->sendMediaGroup($this->getChatId(), $media, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithLocation(int|float $latitude, int|float $longitude, string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendLocation($this->getChatId(), $latitude, $longitude, $keyboard, $extra);
    }

    /**
     * @TODO
     * @return Response
     */
    public function replyWithDice(string $emoji = '🎲', string|array|null $keyboard = null, array $extra = [])
    {
        return $this->sendDice($this->getChatId(), $emoji, $keyboard, $extra);
    }
}