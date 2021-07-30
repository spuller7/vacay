Composer can be installed at getcomposer.org

It is used for the autoload property.

"autoload": {
    ...
}

It forces any class that is created under the project folder "qsai-web-skeleton" to have the namespace "app". This allows those classes to be accessed anywhere in the project without needing to require or include them in the files we want to use them in.

Composer will also be beneficial if we utilize any third party plugins. We can specify which version the plugin we use and every time someone works with the project, it will automatically download that version so every developer has the same development environment.