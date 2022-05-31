<?php

namespace Telegram\BotApi\Traits;

use Telegram\Response;
use Telegram\Support\InputFile;

/**
 * @method Response method(string $method, array $parameters = [])
 */
trait Methods
{
    /**
     * @param array $parameters
     * @param array|string|null $keyboard
     * @param array $extra
     * @return array
     */
    public function mappingParameters(array $parameters = [], array|string|null $keyboard = null, array $extra = []): array
    {
        $parameters = array_merge($parameters, $extra);

        if ($keyboard && isset($this->keyboard)) {
            $parameters['reply_markup'] = is_array($keyboard) ? $this->keyboard->show($keyboard) : $keyboard;
        }

        if (!empty($parameters['text'])) {
            $parameters['text'] = implode("\n", array_map('trim', explode("\n", $parameters['text'])));
            $parameters['text'] = str_replace('<<<', '«', $parameters['text']);
            $parameters['text'] = str_replace('>>>', '»', $parameters['text']);

            // message like ->say('%hello%')
            if (str_starts_with($parameters['text'], '%') && str_ends_with($parameters['text'], '%')) {
                $parameters['text'] = $this->trans(
                    mb_substr($parameters['text'], 1, -1)
                );
            }
        }

        if (!empty($parameters['caption'])) {
            $parameters['caption'] = implode("\n", array_map('trim', explode("\n", $parameters['caption'])));
            $parameters['caption'] = str_replace('<<<', '«', $parameters['caption']);
            $parameters['caption'] = str_replace('>>>', '»', $parameters['caption']);

            // message like ->say('%hello%')
            if (str_starts_with($parameters['caption'], '%') && str_ends_with($parameters['caption'], '%')) {
                $parameters['caption'] = $this->trans(
                    mb_substr($parameters['caption'], 1, -1)
                );
            }
        }

        return $parameters;
    }

    /**
     * @see https://core.telegram.org/bots/api#getupdates
     *
     * @return Response
     */
    public function getUpdates(int $offset = 0, int $limit = 100, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'offset' => $offset,
            'limit' => $limit,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#getme
     *
     * @return Response
     */
    public function getMe()
    {
        $this->method(__FUNCTION__);
    }

    /**
     * @see https://core.telegram.org/bots/api#setwebhook
     *
     * @return Response
     */
    public function setWebhook(string $url, array $extra = [])
    {
        return $this->method(__FUNCTION__, array_merge([
            'url' => $url,
            'max_connections' => 100,
        ], $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#deletewebhook
     *
     * @return Response
     */
    public function deleteWebhook(bool $dropPendingUpdates = false)
    {
        return $this->method(__FUNCTION__, [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getwebhookinfo
     *
     * @return Response
     */
    public function getWebhookInfo()
    {
        return $this->method(__FUNCTION__);
    }

    /**
     * @see https://core.telegram.org/bots/api#logout
     *
     * @return Response
     */
    public function logOut()
    {
        return $this->method(__FUNCTION__);
    }

    /**
     * @see https://core.telegram.org/bots/api#close
     *
     * @return Response
     */
    public function close()
    {
        return $this->method(__FUNCTION__);
    }

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
     * @see https://core.telegram.org/bots/api#sendchataction
     *
     * @return Response
     */
    public function sendChatAction(string|int $chatId, string $action = 'typing')
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'action' => $action,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendmessage
     *
     * @return Response
     */
    public function sendMessage(string|int $chatId, string $text, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'text' => $text,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#forwardmessage
     *
     * @return Response
     */
    public function forwardMessage(string|int $chatId, string|int $fromChatId, string|int $messageId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#copymessage
     *
     * @return Response
     */
    public function copyMessage(string|int $fromChatId, string|int $toChatId, string|int $messageId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $toChatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#sendpoll
     *
     * @return Response
     */
    public function sendPoll(string|int $chatId, string $question, array $options, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'question' => $question,
            'options' => json_encode($options),
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendphoto
     *
     * @return Response
     */
    public function sendPhoto(string|int $chatId, string|InputFile $photo, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'photo' => $photo,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendaudio
     *
     * @return Response
     */
    public function sendAudio(string|int $chatId, string|InputFile $audio, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'audio' => $audio,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#senddocument
     *
     * @return Response
     */
    public function sendDocument(string|int $chatId, string|InputFile $document, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'document' => $document,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendanimation
     *
     * @return Response
     */
    public function sendAnimation(string|int $chatId, string|InputFile $animation, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'animation' => $animation,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendvideo
     *
     * @return Response
     */
    public function sendVideo(string|int $chatId, string|InputFile $video, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'video' => $video,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendvideonote
     *
     * @return Response
     */
    public function sendVideoNote(string|int $chatId, string|InputFile $videoNote, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'video_note' => $videoNote,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendsticker
     *
     * @return Response
     */
    public function sendSticker(string|int $chatId, string|InputFile $sticker, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'sticker' => $sticker,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendvoice
     *
     * @return Response
     */
    public function sendVoice(string|int $chatId, string|InputFile $voice, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'voice' => $voice,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendmediagroup
     *
     * @return Response
     */
    public function sendMediaGroup(string|int $chatId, array $media, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'media' => json_encode($media),
        ], extra: $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendlocation
     *
     * @return Response
     */
    public function sendLocation(string|int $chatId, int|float $latitude, int|float $longitude, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $keyboard, $extra));
    }

    /**
     * Possible `$emoji` values (default `$emoji = 🎲`):
     * - `🎲`, `dice`
     * - `🎯`, `darts`
     * - `🏀`, `basketball`
     * - `⚽️`, `football`
     * - `🎰`, `777`, `slots`
     *
     * @see https://core.telegram.org/bots/api#senddice
     *
     * @return Response
     */
    public function sendDice(string|int $chatId, string $emoji = '🎲', array|string|null $keyboard = null, array $extra = [])
    {
        $emoji = str_ireplace(['dice'], '🎲', $emoji);
        $emoji = str_ireplace(['darts'], '🎯', $emoji);
        $emoji = str_ireplace(['basketball'], '🏀', $emoji);
        $emoji = str_ireplace(['football'], '⚽️', $emoji);
        $emoji = str_ireplace(['777', 'slots'], '🎰', $emoji);

        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'emoji' => $emoji,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#getuserprofilephotos
     *
     * @return Response
     */
    public function getUserProfilePhotos(string|int $userId, int $offset = 0, int $limit = 100)
    {
        return $this->method(__FUNCTION__, [
            'user_id' => $userId,
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getfile
     *
     * @return Response
     */
    public function getFile(string $fileId)
    {
        return $this->method(__FUNCTION__, [
            'file_id' => $fileId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#banchatmember
     *
     * @return Response
     */
    public function banChatMember(string|int $chatId, string|int $userId, int $untilDate, bool $revokeMessages = false)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate,
            'revoke_messages' => $revokeMessages,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#unbanchatmember
     *
     * @return Response
     */
    public function unbanChatMember(string|int $chatId, string|int $userId, bool $onlyIfBanned = false)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'only_if_banned' => $onlyIfBanned,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#restrictchatmember
     *
     * @return Response
     */
    public function restrictChatMember(string|int $chatId, string|int $userId, int $untilDate, array $permissions = [])
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'permissions' => json_encode($permissions),
            'until_date' => $untilDate,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#promotechatmember
     *
     * @return Response
     */
    public function promoteChatMember(string|int $chatId, string|int $userId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatadministratorcustomtitle
     *
     * @return Response
     */
    public function setChatAdministratorCustomTitle(string|int $chatId, string|int $userId, string $title = '')
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'custom_title' => $title,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatpermissions
     *
     * @return Response
     */
    public function setChatPermissions(string|int $chatId, array $permissions)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'permissions' => json_encode($permissions),
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#exportchatinvitelink
     *
     * @return Response
     */
    public function exportChatInviteLink(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#createchatinvitelink
     *
     * @return Response
     */
    public function createChatInviteLink(string|int $chatId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#editchatinvitelink
     *
     * @return Response
     */
    public function editChatInviteLink(string|int $chatId, string $inviteLink, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#revokechatinvitelink
     *
     * @return Response
     */
    public function revokeChatInviteLink(string|int $chatId, string $inviteLink)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#approvechatjoinrequest
     *
     * @return Response
     */
    public function approveChatJoinRequest(string|int $chatId, string|int $userId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#declinechatjoinrequest
     *
     * @return Response
     */
    public function declineChatJoinRequest(string|int $chatId, string|int $userId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatphoto
     *
     * @return Response
     */
    public function setChatPhoto(string|int $chatId, InputFile $photo)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'photo' => $photo,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#deletechatphoto
     *
     * @return Response
     */
    public function deleteChatPhoto(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchattitle
     *
     * @return Response
     */
    public function setChatTitle(string|int $chatId, string $title)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'title' => $title,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatdescription
     *
     * @return Response
     */
    public function setChatDescription(string|int $chatId, string $description = '')
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'description' => $description,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#pinchatmessage
     *
     * @return Response
     */
    public function pinChatMessage(string|int $chatId, string|int $messageId, bool $disableNotification = false)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => $disableNotification,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#unpinchatmessage
     *
     * @return Response
     */
    public function unpinChatMessage(string|int $chatId, string|int $messageId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#unpinallchatmessages
     *
     * @return Response
     */
    public function unpinAllChatMessages(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#leavechat
     *
     * @return Response
     */
    public function leaveChat(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getchat
     *
     * @return Response
     */
    public function getChat(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getchatadministrators
     *
     * @return Response
     */
    public function getChatAdministrators(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getchatmembercount
     *
     * @return Response
     */
    public function getChatMemberCount(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getchatmember
     *
     * @return Response
     */
    public function getChatMember(string|int $chatId, string|int $userId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatstickerset
     *
     * @return Response
     */
    public function setChatStickerSet(string|int $chatId, string $stickerSetName)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'sticker_set_name' => $stickerSetName,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#deletechatstickerset
     *
     * @return Response
     */
    public function deleteChatStickerSet(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#editmessagetext
     *
     * @return Response
     */
    public function editMessageText(string|int $messageId, string|int $chatId, string $text, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#editmessagecaption
     *
     * @return Response
     */
    public function editMessageCaption(string|int $messageId, string|int $chatId, string $caption, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'caption' => $caption,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#editmessagemedia
     *
     * @return Response
     */
    public function editMessageMedia(string|int $messageId, string|int $chatId, array $media, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'media' => json_encode($media),
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#editmessagereplymarkup
     *
     * @return Response
     */
    public function editMessageReplyMarkup(string|int $messageId, string|int $chatId, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#stoppoll
     *
     * @return Response
     */
    public function stopPoll(string|int $messageId, string|int $chatId, array|string|null $keyboard = null)
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ], $keyboard));
    }

    /**
     * @see https://core.telegram.org/bots/api#deletemessage
     *
     * @return Response
     */
    public function deleteMessage(string|int $messageId, string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getstickerset
     *
     * @return Response
     */
    public function getStickerSet(string $name)
    {
        return $this->method(__FUNCTION__, [
            'name' => $name,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#deletestickerfromset
     *
     * @return Response
     */
    public function deleteStickerFromSet(string $sticker)
    {
        return $this->method(__FUNCTION__, [
            'sticker' => $sticker,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#uploadstickerfile
     *
     * @return Response
     */
    public function uploadStickerFile(string|int $userId, InputFile $image)
    {
        return $this->method(__FUNCTION__, [
            'user_id' => $userId,
            'png_sticker' => $image,
        ], true);
    }

    /**
     * @see https://core.telegram.org/bots/api#createnewstickerset
     *
     * @return Response
     */
    public function createNewStickerSet(string|int $userId, string $name, string $title, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'user_id' => $userId,
            'name' => $name,
            'title' => $title,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#addstickertoset
     *
     * @return Response
     */
    public function addStickerToSet(string|int $userId, string $name, string $emojis, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'user_id' => $userId,
            'name' => $name,
            'emojis' => $emojis,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#setstickerpositioninset
     *
     * @return Response
     */
    public function setStickerPositionInSet(string $sticker, int $position)
    {
        return $this->method(__FUNCTION__, [
            'sticker' => $sticker,
            'position' => $position,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setstickersetthumb
     *
     * @return Response
     */
    public function setStickerSetThumb(string|int $userId, string $name, InputFile|string $thumb)
    {
        return $this->method(__FUNCTION__, [
            'user_id' => $userId,
            'name' => $name,
            'thumb' => $thumb,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendgame
     *
     * @return Response
     */
    public function sendGame(string|int $chatId, string $name, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'game_short_name' => $name,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#setgamescore
     *
     * @return Response
     */
    public function setGameScore(string|int $userId, int $score, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'user_id' => $userId,
            'score' => $score,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#getgamehighscores
     *
     * @return Response
     */
    public function getGameHighScores(string|int $userId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'user_id' => $userId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#answercallbackquery
     *
     * @return Response
     */
    public function answerCallbackQuery(array $parameters = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'callback_query_id' => $this->payload('callback_query.id'),
        ], extra: $parameters));
    }

    /**
     * @see https://core.telegram.org/bots/api#answerinlinequery
     *
     * @return Response
     */
    public function answerInlineQuery(array $results = [], array $extra = [])
    {
        return $this->method(__FUNCTION__, array_merge([
            'inline_query_id' => $this->payload('inline_query.id'),
            'results' => json_encode($results),
        ], $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#setmycommands
     *
     * @return Response
     */
    public function setMyCommands(array $commands, ?array $scope = null, ?string $language = null)
    {
        return $this->method(__FUNCTION__, array_filter([
            'commands' => json_encode($commands),
            'scope' => $scope ? json_encode($scope) : '',
            'language_code' => $language ?? '',
        ], 'strlen'));
    }

    /**
     * @see https://core.telegram.org/bots/api#deletemycommands
     *
     * @return Response
     */
    public function deleteMyCommands(?array $scope = null, ?string $language = null)
    {
        return $this->method(__FUNCTION__, array_filter([
            'scope' => $scope ? json_encode($scope) : null,
            'language_code' => $language,
        ], 'strlen'));
    }

    /**
     * @see https://core.telegram.org/bots/api#getmycommands
     *
     * @return Response
     */
    public function getMyCommands()
    {
        return $this->method(__FUNCTION__);
    }
}
