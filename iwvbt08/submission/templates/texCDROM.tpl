\documentclass[a4paper,11pt]{report}
\usepackage[latin1]{inputenc}
\usepackage[T1]{fontenc}
\usepackage[english]{babel}
\usepackage{hyperref}
\usepackage{makeidx}

\makeindex
\textwidth 15cm
\hoffset -1cm
\textheight 24cm
\voffset -2cm

\begin{document}

\begin{center}

{\huge {CONF_NAME}}\\
{\Huge CDROM}
\bigskip
\bigskip
\bigskip

%\hyperlink{foreword}{Foreword}\\
%\bigskip
%\bigskip

%\hyperlink{themes}{Thematic list of papers}\\
%\bigskip
%\bigskip

\hyperlink{conf-program}{Program}\\
\bigskip
\bigskip

\hyperlink{listAbstracts}{List of abstracts}\\
\bigskip
\bigskip

\hyperlink{indexAuthors}{Index of authors}\\

\end{center}

\newpage

\include{program}
\include{abstracts}

% \twocolumn

% \twocolumn[
\centerline{\Huge \bf Index of authors}
% ~\\]

\section*{~}
\hypertarget{indexAuthors}{~}

\printindex

\end{document}
