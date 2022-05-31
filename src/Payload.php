<?php

namespace Telegram;

use Telegram\Support\Collection;

class Payload extends Collection
{
    protected $_defaultChatForReply = false;
    protected $_defaultLanguageId = false;

    public function __construct(array $payload, protected Bot &$bot)
    {
        $this->items = $payload;
        $this->bot = $bot;
    }

    public function getChatForReply(): int|null
    {
        if ($this->_defaultChatForReply !== false) {
            return $this->_defaultChatForReply;
        }

        if ($this->isCallbackQuery()) {
            $this->_defaultChatForReply = $this->get('*.message.chat.id');
            // $this->languageId = $this->get('*.message.chat.language_code', $this->get('*.message.reply_to_message.from.language_code'));

            return $this->_defaultChatForReply;
        } else {
            $this->_defaultChatForReply = $this->get('*.from.id', $this->get('*.user.id', $this->get('*.chat.id')));
            // $this->languageId = $this->get('*.from.language_code', $this->get('*.user.language_code'));

            return $this->_defaultChatForReply;
        }

        return null;
    }

    public function getLanguageCode(): string|null
    {
        if ($this->_defaultLanguageId !== false) {
            return $this->_defaultLanguageId;
        }

        if ($this->isCallbackQuery()) {
            $this->_defaultLanguageId = $this->get('*.message.chat.language_code', $this->get('*.message.reply_to_message.from.language_code'));
            return $this->_defaultLanguageId;
        } else {
            $this->_defaultLanguageId = $this->get('*.from.language_code', $this->get('*.user.language_code'));
            return $this->_defaultLanguageId;
        }

        return null;
    }

    /**
     * @return boolean
     */
    public function isMessage(): bool
    {
        return $this->has('message');
    }

    /**
     * @return Collection
     */
    public function getMessage()
    {
        return new Collection($this->get('message'));
    }

    public function isEditedMessage(): bool
    {
        return $this->has('edited_message');
    }

    /**
     * @return Collection
     */
    public function getEditedMessage()
    {
        return new Collection($this->get('edited_message'));
    }

    public function isChannelPost(): bool
    {
        return $this->has('channel_post');
    }

    /**
     * @return Collection
     */
    public function getChannelPost()
    {
        return new Collection($this->get('channel_post'));
    }

    public function isEditedChannelPost(): bool
    {
        return $this->has('edited_channel_post');
    }

    /**
     * @return Collection
     */
    public function getEditedChannelPost()
    {
        return new Collection($this->get('edited_channel_post'));
    }

    /**
     * @return boolean
     */
    public function isInlineQuery(): bool
    {
        return $this->has('inline_query');
    }

    /**
     * @return boolean
     */
    public function isInline(): bool
    {
        return $this->isInlineQuery();
    }

    /**
     * @return Collection
     */
    public function getInlineQuery()
    {
        return new Collection($this->get('inline_query'));
    }

    /**
     * @return boolean
     */
    public function isChosenInlineResult(): bool
    {
        return $this->has('chosen_inline_result');
    }

    /**
     * @return Collection
     */
    public function getChosenInlineResult()
    {
        return new Collection($this->get('chosen_inline_result'));
    }

    /**
     * @return boolean
     */
    public function isCallbackQuery(): bool
    {
        return $this->has('callback_query');
    }

    /**
     * @return boolean
     */
    public function isCallback(): bool
    {
        return $this->isCallbackQuery();
    }

    /**
     * @return Collection
     */
    public function getCallbackQuery()
    {
        return new Collection($this->get('callback_query'));
    }

    public function isShippingQuery(): bool
    {
        return $this->has('shipping_query');
    }

    /**
     * @return Collection
     */
    public function getShippingQuery()
    {
        return new Collection($this->get('shipping_query'));
    }

    /**
     * @return boolean
     */
    public function isPreCheckoutQuery(): bool
    {
        return $this->has('pre_checkout_query');
    }

    /**
     * @return Collection
     */
    public function getPreCheckoutQuery()
    {
        return new Collection($this->get('pre_checkout_query'));
    }

    /**
     * @return boolean
     */
    public function isPoll(): bool
    {
        return $this->has('poll');
    }

    /**
     * @return Collection
     */
    public function getPoll()
    {
        return new Collection($this->get('poll'));
    }

    /**
     * @return boolean
     */
    public function isPollAnswer(): bool
    {
        return $this->has('poll_answer');
    }

    /**
     * @return Collection
     */
    public function getPollAnswer()
    {
        return new Collection($this->get('poll_answer'));
    }

    /**
     * @return boolean
     */
    public function isCommand(): bool
    {
        if (!$this->isMessage() && !$this->isEditedMessage()) {
            return false;
        }

        if (!$text = $this->get('*.text', false)) {
            return false;
        }

        return in_array(mb_substr($text, 0, 1, 'utf-8'), $this->bot->config('bot.prefix'));
    }

    /**
     * @return int|string
     */
    public function getCommand()
    {
        return $this->get('*.text');
    }

    /**
     * @return boolean
     */
    public function isBot(): bool
    {
        return $this->has('*.from.is_bot');
    }

    /**
     * @return boolean
     */
    public function isSticker(): bool
    {
        return $this->has('*.sticker');
    }

    /**
     * @return Collection
     */
    public function getSticker()
    {
        return new Collection($this->get('*.sticker'));
    }

    /**
     * @return boolean
     */
    public function isVoice(): bool
    {
        return $this->has('*.voice');
    }

    /**
     * @return Collection
     */
    public function getVoice()
    {
        return new Collection($this->get('*.voice'));
    }

    /**
     * @return boolean
     */
    public function isAnimation(): bool
    {
        return $this->has('*.animation');
    }

    /**
     * @return Collection
     */
    public function getAnimation()
    {
        return new Collection($this->get('*.animation'));
    }

    /**
     * @return boolean
     */
    public function isDocument(): bool
    {
        return $this->has('*.document');
    }

    /**
     * @return Collection
     */
    public function getDocument()
    {
        return new Collection($this->get('*.document'));
    }

    /**
     * @return boolean
     */
    public function isAudio(): bool
    {
        return $this->has('*.audio');
    }

    /**
     * @return Collection
     */
    public function getAudio()
    {
        return new Collection($this->get('*.audio'));
    }

    /**
     * @return boolean
     */
    public function isPhoto(): bool
    {
        return $this->has('*.photo');
    }

    /**
     * @return Collection
     */
    public function getPhoto()
    {
        return new Collection($this->get('*.photo'));
    }

    /**
     * @return boolean
     */
    public function isVideo(): bool
    {
        return $this->has('*.video');
    }

    /**
     * @return Collection
     */
    public function getVideo()
    {
        return new Collection($this->get('*.video'));
    }

    /**
     * @return boolean
     */
    public function isVideoNote(): bool
    {
        return $this->has('*.video_note');
    }

    /**
     * @return Collection
     */
    public function getVideoNote()
    {
        return new Collection($this->get('*.video_note'));
    }

    /**
     * @return boolean
     */
    public function isContact(): bool
    {
        return $this->has('*.contact');
    }

    /**
     * @return Collection
     */
    public function getContact()
    {
        return new Collection($this->get('*.contact'));
    }

    /**
     * @return boolean
     */
    public function isLocation(): bool
    {
        return $this->has('*.location');
    }

    /**
     * @return Collection
     */
    public function getLocation()
    {
        return new Collection($this->get('*.location'));
    }

    /**
     * @return boolean
     */
    public function isVenue(): bool
    {
        return $this->has('*.venue');
    }

    /**
     * @return Collection
     */
    public function getVenue()
    {
        return new Collection($this->get('*.venue'));
    }

    /**
     * @return boolean
     */
    public function isDice(): bool
    {
        return $this->has('*.dice');
    }

    /**
     * @return Collection
     */
    public function getDice()
    {
        return new Collection($this->get('*.dice'));
    }

    /**
     * @return boolean
     */
    public function isNewChatMembers(): bool
    {
        return $this->has('*.new_chat_members');
    }

    /**
     * @return Collection
     */
    public function getNewChatMembers()
    {
        return new Collection($this->get('*.new_chat_members'));
    }

    /**
     * @return boolean
     */
    public function isLeftChatMember(): bool
    {
        return $this->has('*.left_chat_member');
    }

    /**
     * @return Collection
     */
    public function getLeftChatMember()
    {
        return new Collection($this->get('*.left_chat_member'));
    }

    /**
     * @return boolean
     */
    public function isNewChatTitle(): bool
    {
        return $this->has('*.new_chat_title');
    }

    /**
     * @return int|string
     */
    public function getNewChatTitle()
    {
        return $this->get('*.new_chat_title');
    }

    /**
     * @return boolean
     */
    public function isNewChatPhoto(): bool
    {
        return $this->has('*.new_chat_photo');
    }

    /**
     * @return Collection
     */
    public function getNewChatPhoto()
    {
        return new Collection($this->get('*.new_chat_photo'));
    }

    /**
     * @return boolean
     */
    public function isDeleteChatPhoto(): bool
    {
        return $this->has('*.delete_chat_photo');
    }

    /**
     * @return boolean
     */
    public function isChannelChatCreated(): bool
    {
        return $this->has('*.channel_chat_created');
    }

    /**
     * @return boolean
     */
    public function isMigrateToChatId(): bool
    {
        return $this->has('*.migrate_to_chat_id');
    }

    /**
     * @return int|string
     */
    public function getMigrateToChatId()
    {
        return $this->get('*.migrate_to_chat_id');
    }

    /**
     * @return boolean
     */
    public function isMigrateFromChatId(): bool
    {
        return $this->has('*.migrate_from_chat_id');
    }

    /**
     * @return int|string
     */
    public function getMigrateFromChatId()
    {
        return $this->get('*.migrate_from_chat_id');
    }

    /**
     * @return boolean
     */
    public function isPinnedMessage(): bool
    {
        return $this->has('*.pinned_message');
    }

    /**
     * @return Collection
     */
    public function getPinnedMessage()
    {
        return new Collection($this->get('*.pinned_message'));
    }

    /**
     * @return boolean
     */
    public function isInvoice(): bool
    {
        return $this->has('*.invoice');
    }

    /**
     * @return Collection
     */
    public function getInvoice()
    {
        return new Collection($this->get('*.invoice'));
    }

    /**
     * @return boolean
     */
    public function isSucessfulPayment(): bool
    {
        return $this->has('*.successful_payment');
    }

    /**
     * @return Collection
     */
    public function getSucessfulPayment()
    {
        return new Collection($this->get('*.successful_payment'));
    }

    /**
     * @return boolean
     */
    public function isConnectedWebsite(): bool
    {
        return $this->has('*.connected_website');
    }

    /**
     * @return Collection|int|string
     */
    public function getConnectedWebsite()
    {
        return $this->get('*.connected_website');
    }

    /**
     * @return boolean
     */
    public function isPassportData(): bool
    {
        return $this->has('*.passport_data');
    }

    /**
     * @return Collection
     */
    public function getPassportData()
    {
        return new Collection($this->get('*.passport_data'));
    }

    /**
     * @return boolean
     */
    public function isReplyMarkup(): bool
    {
        return $this->has('*.reply_markup');
    }

    /**
     * @return Collection
     */
    public function getReplyMarkup()
    {
        return new Collection($this->get('*.reply_markup'));
    }

    /**
     * @return boolean
     */
    public function isReply(): bool
    {
        return $this->has('*.reply_to_message');
    }

    /**
     * @return Collection
     */
    public function getReply()
    {
        return new Collection($this->get('*.reply_to_message'));
    }

    /**
     * @return Collection
     */
    public function getFrom()
    {
        return new Collection($this->get('*.from'));
    }

    /**
     * @return Collection
     */
    public function getChat()
    {
        return new Collection($this->get('*.chat'));
    }

    /**
     * @return boolean
     */
    public function isCaption(): bool
    {
        return $this->has('*.caption');
    }

    /**
     * @return int|string
     */
    public function getCaption()
    {
        return $this->get('*.reply_to_message');
    }

    /**
     * @return int|string
     */
    public function getText()
    {
        return $this->get('*.text');
    }

    /**
     * @return int|string
     */
    public function getTextOrCaption()
    {
        return $this->get('*.text', $this->get('*.caption'));
    }

    /**
     * @return int|string
     */
    public function getData()
    {
        return $this->get('callback_query.data');
    }

    /**
     * @return int|string
     */
    public function getQuery()
    {
        return $this->get('inline_query.query');
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->get('update_id');
    }

    /**
     * @return int|string
     */
    public function getMessageId()
    {
        return $this->get('*.message_id');
    }

    /**
     * @return int|string
     */
    public function getCallbackId()
    {
        return $this->get('callback_query.id');
    }

    /**
     * @return int|string
     */
    public function getPollId()
    {
        return $this->get('poll.id');
    }

    /**
     * @return int|string
     */
    public function getPollAnswerId()
    {
        return $this->get('poll_answer.poll_id');
    }

    /**
     * @return int|string
     */
    public function getInlineId()
    {
        return $this->get('inline_query.id');
    }

    /**
     * @return boolean
     */
    public function isForward(): bool
    {
        return $this->has('*.forward_date') || $this->has('*.forward_from');
    }

    /**
     * @return boolean
     */
    public function isSuperGroup(): bool
    {
        return $this->get('*.chat.type') == 'supergroup';
    }

    /**
     * @return boolean
     */
    public function isGroup(): bool
    {
        return $this->get('*.chat.type') == 'group';
    }

    /**
     * @return boolean
     */
    public function isChannel(): bool
    {
        return $this->get('*.chat.type') == 'channel';
    }

    /**
     * @return boolean
     */
    public function isPrivate(): bool
    {
        return $this->get('*.chat.type') == 'private';
    }
}