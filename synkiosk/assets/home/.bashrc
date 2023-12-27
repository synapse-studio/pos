# ~/.bashrc: executed by bash(1) for non-login shells.
# see /usr/share/doc/bash/examples/startup-files (in the package bash-doc)
# for examples

# If not running interactively, don't do anything
[ -z "$PS1" ] && return

# don't put duplicate lines in the history. See bash(1) for more options
# ... or force ignoredups and ignorespace
HISTCONTROL=ignoredups:ignorespace

# append to the history file, don't overwrite it
shopt -s histappend

# for setting history length see HISTSIZE and HISTFILESIZE in bash(1)
HISTSIZE=1000
HISTFILESIZE=2000

# check the window size after each command and, if necessary,
# update the values of LINES and COLUMNS.
shopt -s checkwinsize

# make less more friendly for non-text input files, see lesspipe(1)
[ -x /usr/bin/lesspipe ] && eval "$(SHELL=/bin/sh lesspipe)"

# set variable identifying the chroot you work in (used in the prompt below)
if [ -z "$debian_chroot" ] && [ -r /etc/debian_chroot ]; then
    debian_chroot=$(cat /etc/debian_chroot)
fi

# set a fancy prompt (non-color, unless we know we "want" color)
case "$TERM" in
    xterm-color) color_prompt=yes;;
esac

# uncomment for a colored prompt, if the terminal has the capability; turned
# off by default to not distract the user: the focus in a terminal window
# should be on the output of commands, not on the prompt
#force_color_prompt=yes

if [ -n "$force_color_prompt" ]; then
    if [ -x /usr/bin/tput ] && tput setaf 1 >&/dev/null; then
	# We have color support; assume it's compliant with Ecma-48
	# (ISO/IEC-6429). (Lack of such support is extremely rare, and such
	# a case would tend to support setf rather than setaf.)
	color_prompt=yes
    else
	color_prompt=
    fi
fi

if [ "$color_prompt" = yes ]; then
    PS1='${debian_chroot:+($debian_chroot)}\[\033[01;32m\]\u@\h\[\033[00m\]:\[\033[01;34m\]\w\[\033[00m\]\$ '
else
    PS1='${debian_chroot:+($debian_chroot)}\u@\h:\w\$ '
fi
unset color_prompt force_color_prompt

# If this is an xterm set the title to user@host:dir
case "$TERM" in
xterm*|rxvt*)
    PS1="\[\e]0;${debian_chroot:+($debian_chroot)}\u@\h: \w\a\]$PS1"
    ;;
*)
    ;;
esac

# enable color support of ls and also add handy aliases
if [ -x /usr/bin/dircolors ]; then
    test -r ~/.dircolors && eval "$(dircolors -b ~/.dircolors)" || eval "$(dircolors -b)"
    alias ls='ls --color=auto'
    #alias dir='dir --color=auto'
    #alias vdir='vdir --color=auto'

    alias grep='grep --color=auto'
    alias fgrep='fgrep --color=auto'
    alias egrep='egrep --color=auto'
fi

# some more ls aliases
alias ll='ls -alF'
alias la='ls -A'
alias l='ls -CF'

# Alias definitions.
# You may want to put all your additions into a separate file like
# ~/.bash_aliases, instead of adding them here directly.
# See /usr/share/doc/bash-doc/examples in the bash-doc package.

if [ -f ~/.bash_aliases ]; then
    . ~/.bash_aliases
fi

# enable programmable completion features (you don't need to enable
# this, if it's already enabled in /etc/bash.bashrc and /etc/profile
# sources /etc/bash.bashrc).
#if [ -f /etc/bash_completion ] && ! shopt -oq posix; then
#    . /etc/bash_completion
#fi

# Include Drush bash customizations.
if [ -f "/var/www/.drush/drush.bashrc" ] ; then
  source /var/www/.drush/drush.bashrc
fi
# Include Drush completion.
if [ -f "/var/www/.drush/drush.complete.sh" ] ; then
  source /var/www/.drush/drush.complete.sh
fi

# Include Drush prompt customizations.
if [ -f "/var/www/.drush/drush.prompt.sh" ] ; then
  source /var/www/.drush/drush.prompt.sh
fi

# check the window size after each command and, if necessary,
# update the values of LINES and COLUMNS.
shopt -s checkwinsize
# If not running interactively, don't do anything
[ -z "$PS1" ] && return

#-------
# Цвета
#-------
txtblk='\e[0;30m' # Black - Regular
txtred='\e[0;31m' # Red
txtgrn='\e[0;32m' # Green
txtylw='\e[0;33m' # Yellow
txtblu='\e[0;34m' # Blue
txtpur='\e[0;35m' # Purple
txtcyn='\e[0;36m' # Cyan
txtwht='\e[0;37m' # White
bldblk='\e[1;30m' # Black - Bold
bldred='\e[1;31m' # Red
bldgrn='\e[1;32m' # Green
bldylw='\e[1;33m' # Yellow
bldblu='\e[1;34m' # Blue
bldpur='\e[1;35m' # Purple
bldcyn='\e[1;36m' # Cyan
bldwht='\e[1;37m' # White
unkblk='\e[4;30m' # Black - Underline
undred='\e[4;31m' # Red
undgrn='\e[4;32m' # Green
undylw='\e[4;33m' # Yellow
undblu='\e[4;34m' # Blue
undpur='\e[4;35m' # Purple
undcyn='\e[4;36m' # Cyan
undwht='\e[4;37m' # White
bakblk='\e[40m'   # Black - Background
bakred='\e[41m'   # Red
badgrn='\e[42m'   # Green
bakylw='\e[43m'   # Yellow
bakblu='\e[44m'   # Blue
bakpur='\e[45m'   # Purple
bakcyn='\e[46m'   # Cyan
bakwht='\e[47m'   # White
NC='\e[0m'    # Text Reset

# некоторые цвета:
red='\e[0;31m'
RED='\e[1;31m'
blue='\e[0;34m'
BLUE='\e[1;34m'
cyan='\e[0;36m'
CYAN='\e[1;36m'

#-----------------------
# Greeting, motd etc...
#-----------------------

# Дополнительные сведения о системе
function ii() {
  IP=$(ifconfig  | grep 'inet addr:'| grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $1}')
  echo -e "\n${bldblu}`hostname`$NC $IP"
  echo -e "${bldblu}Дата:   $NC `date`"
  echo -e "${bldblu}Аптайм:$NC `uptime`"
  echo -e "${bldblu}Инфо:   $NC $(uname -srvm)"
  echo -e "\n${bldblu}Память :$NC " ; free
  echo -e "\n${bldblu}Открытые сеансы:$NC " ; w -h
  echo
}
ii # init func

#-----------------------------
# Некоторые настройки
#-----------------------------
shopt -s histappend           # Так история команд будет добавляться к старой, а не перезаписывать ее,
PROMPT_COMMAND='history -a'   # и запись будет происходить каждый раз в момент отображения подсказки bash.
shopt -s cdspell              # Таким образом ошибки в написании (например, ect вместо etc) будут исправляться
export HISTIGNORE="&:l:l[aslxkcurtm]:bg:fg:exit" # Это позволит избавиться от дубликатов в истории команд
shopt -s cmdhist # многострочные команды будут записываться в одну строку
umask 022
# eval "`dircolors`" # цветные файлы в ls

#-------------------------
# Псевдонимы
#-------------------------
alias e="extract"
alias drush="/usr/local/bin/drush"
export PATH="$HOME/.local/bin/:$PATH"

export LS_OPTIONS='--color=always --human'
alias l='ls $LS_OPTIONS -hF'
alias ls='ls $LS_OPTIONS -hF'         # выделить различные типы файлов цветом
alias la='ls $LS_OPTIONS -Al'         # показать скрытые файлы
alias ll='ls $LS_OPTIONS -l'          # показать подробно
alias lx='ls $LS_OPTIONS -lXB'        # сортировка по расширению
alias lk='ls $LS_OPTIONS -lSr'        # сортировка по размеру
alias lc='ls $LS_OPTIONS -lcr'        # сортировка по времени изменения
alias lu='ls $LS_OPTIONS -lur'        # сортировка по времени последнего обращения
alias lr='ls $LS_OPTIONS -lR'         # рекурсивный обход подкаталогов
alias lt='ls $LS_OPTIONS -ltr'        # сортировка по дате
alias lm='ls $LS_OPTIONS -al | more'  # вывод через 'more'
alias tree='tree -Csu'                # альтернатива 'ls'

# enable color support of ls and also add handy aliases
if [ "$TERM" != "dumb" ]; then
    eval "`dircolors -b`"
    alias ls='ls --color=auto'
    #alias dir='ls --color=auto --format=vertical'
    #alias vdir='ls --color=auto --format=long'
fi

#--------
# Prompt
#--------
PS1="\[${bldred}\]\u@\h:\[${NC}\]\[${bldblu}\]\w\[${NC}\]\$ "

#-----------
# Разарихивировать файл
#-----------
function extract() {
  if [ -f $1 ] ; then
    case $1 in
      *.tar.bz2)   tar xvjf $1     ;;
      *.tar.gz)    tar xvzf $1     ;;
      *.bz2)       bunzip2 $1      ;;
      *.rar)       unrar x $1      ;;
      *.gz)        gunzip $1       ;;
      *.tar)       tar xvf $1      ;;
      *.tbz2)      tar xvjf $1     ;;
      *.tgz)       tar xvzf $1     ;;
      *.zip)       unzip $1        ;;
      *.Z)         uncompress $1   ;;
      *.7z)        7z x $1         ;;
      *)           echo "'$1' cannot be extracted via >extract<" ;;
    esac
  else
    echo "'$1' is not a valid file"
  fi
}

# google-chrome --kiosk "http://2630-k-dlya.n23.s1dev.ru/" 
